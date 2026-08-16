<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\BillingCycle;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\SubscribeRequest;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\UserSubscription;
use App\Services\PlanUsageService;
use App\Services\SubscriptionService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PlanUsageService $planUsageService,
        private WalletService $walletService,
    ) {}

    public function plans(): View
    {
        $plans = SubscriptionPlan::query()
            ->where('status', true)
            ->with(['features', 'prices'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $current = $this->activeSubscription();

        $maxHalfYearlyDiscount = $plans->flatMap->prices
            ->where('billing_cycle', BillingCycle::HalfYearly)
            ->max('discount');

        $maxYearlyDiscount = $plans->flatMap->prices
            ->where('billing_cycle', BillingCycle::Yearly)
            ->max('discount');

        // Build per-plan prorated upgrade/downgrade math for each billing cycle so the UI
        // can show "Switch for $X (prorated)" labels and confirm dialogs without round-trips.
        $proration = [];
        if ($current) {
            foreach ($plans as $p) {
                foreach ([BillingCycle::Monthly, BillingCycle::HalfYearly, BillingCycle::Yearly] as $cycle) {
                    $proration[$p->id][$cycle->value] = $this->subscriptionService->calculateProration($current, $p, $cycle);
                }
            }
        }

        return view('frontend.user.subscription.plans', compact('plans', 'current', 'maxHalfYearlyDiscount', 'maxYearlyDiscount', 'proration'));
    }

    public function current(): View
    {
        $user = auth()->user();

        $subscription = $this->activeSubscription()?->load([
            'plan.features',
            'plan.prices',
            'transactions' => fn ($query) => $query->latest()->limit(12),
        ]);

        $stats = $this->buildUserStats($user);
        $usage = $this->planUsageService->build($user, $subscription?->plan);

        $upgradePlan = null;

        if ($subscription) {
            $upgradePlan = SubscriptionPlan::query()
                ->where('status', true)
                ->where('id', '!=', $subscription->subscription_plan_id)
                ->where('sort_order', '>', (int) $subscription->plan->sort_order)
                ->with(['prices', 'features'])
                ->orderBy('sort_order')
                ->first();
        }

        return view('frontend.user.subscription.current', compact('subscription', 'stats', 'usage', 'upgradePlan'));
    }

    /**
     * @return array<string, int|float>
     */
    private function buildUserStats($user): array
    {
        $completed = TrxStatus::COMPLETED->value;

        return [
            'deposits' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('trx_type', TrxType::DEPOSIT->value)
                ->where('status', $completed)
                ->sum('amount'),
            'sent' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('trx_type', TrxType::SEND_MONEY->value)
                ->where('status', $completed)
                ->sum('amount'),
            'referrals' => (int) $user->referrals()->count(),
            'vouchers'  => (int) Transaction::query()
                ->where('user_id', $user->id)
                ->where('trx_type', TrxType::VOUCHER->value)
                ->where('status', $completed)
                ->count(),
        ];
    }

    public function history(): View
    {
        $subscriptions = auth()->user()
            ->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('frontend.user.subscription.history', compact('subscriptions'));
    }

    /**
     * Checkout page — confirm a new subscription, upgrade, or downgrade with prorated math.
     */
    public function checkout(SubscriptionPlan $plan, ?string $cycle = null): View|RedirectResponse
    {
        if (! $plan->status) {
            notifyEvs('error', __('This plan is currently unavailable.'));

            return redirect()->route('user.subscription.plans');
        }

        $user = auth()->user();
        $plan->load(['features', 'prices']);

        $billingCycle = BillingCycle::tryFrom((string) $cycle) ?? BillingCycle::Monthly;
        $current      = $this->activeSubscription();
        $isSwitch     = (bool) $current;

        // Block if user is already on this exact plan + cycle
        $currentCycleVal = $current?->billing_cycle instanceof BillingCycle
            ? $current->billing_cycle->value
            : (string) $current?->billing_cycle;

        if ($current
            && (int) $current->subscription_plan_id === (int) $plan->id
            && $currentCycleVal                     === $billingCycle->value) {
            notifyEvs('info', __('You are already on this plan and billing cycle.'));

            return redirect()->route('user.subscription.current');
        }

        // Build cycle options with proration each
        $cyclesData = [];
        foreach ([BillingCycle::Monthly, BillingCycle::HalfYearly, BillingCycle::Yearly] as $c) {
            $row   = $plan->prices->firstWhere('billing_cycle', $c);
            $price = (float) ($row?->price ?? 0);

            if ($current) {
                $proration = $this->subscriptionService->calculateProration($current, $plan, $c);
            } else {
                $startsTrial = $plan->trial_days > 0 && $price > 0;

                $proration = [
                    'credit'         => 0.0,
                    'charge'         => $startsTrial ? 0.0 : $price,
                    'new_plan_price' => $price,
                    'remaining_days' => 0,
                    'total_days'     => 0,
                ];
            }

            $cyclesData[$c->value] = [
                'cycle'     => $c,
                'label'     => $c->label(),
                'price'     => $price,
                'discount'  => $row?->discount ?? 0,
                'proration' => $proration,
            ];
        }

        $wallet        = $this->walletService->getDefaultWallet($user);
        $walletBalance = (float) ($wallet?->balance ?? 0);
        $currencyCode  = $wallet?->currency?->code ?? siteCurrency('code');

        return view('frontend.user.subscription.checkout', compact(
            'plan', 'billingCycle', 'cyclesData', 'current', 'isSwitch', 'walletBalance', 'currencyCode'
        ));
    }

    public function subscribe(SubscribeRequest $request): RedirectResponse
    {
        $plan         = SubscriptionPlan::findOrFail($request->validated('plan_id'));
        $billingCycle = BillingCycle::from($request->validated('billing_cycle'));
        $user         = auth()->user();

        $existingActive = $user->subscriptions()
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->first();
        $isSwitch = (bool) $existingActive;

        try {
            $subscription = $this->subscriptionService->subscribe($user, $plan, $billingCycle);

            $message = match (true) {
                $isSwitch                                => __('You have switched to the :plan plan.', ['plan' => $plan->name]),
                $subscription->status->value === 'trial' => __('Your free trial for the :plan plan has started.', ['plan' => $plan->name]),
                default                                  => __('You have successfully subscribed to the :plan plan!', ['plan' => $plan->name]),
            };

            notifyEvs('success', $message);

            return redirect()->route('user.subscription.current');
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return redirect()->route('user.subscription.plans');
        } catch (\Throwable $e) {
            notifyEvs('error', __('Subscription failed. Please try again.'));

            return redirect()->route('user.subscription.plans');
        }
    }

    public function cancel(UserSubscription $subscription): RedirectResponse
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        try {
            $this->subscriptionService->cancel($subscription);
            notifyEvs('success', __('Your subscription has been cancelled.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('user.subscription.current');
    }

    public function renew(UserSubscription $subscription): RedirectResponse
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        try {
            $this->subscriptionService->renew($subscription);
            notifyEvs('success', __('Your subscription has been renewed successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        } catch (\Throwable $e) {
            notifyEvs('error', __('Renewal failed. Please try again.'));
        }

        return redirect()->route('user.subscription.current');
    }

    private function activeSubscription(): ?UserSubscription
    {
        return auth()->user()
            ->subscriptions()
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->with(['plan.features', 'plan.prices'])
            ->latest()
            ->first();
    }
}
