<?php

namespace App\Http\Controllers\Backend\Subscription;

use App\Enums\BillingCycle;
use App\Http\Controllers\Backend\BaseController;
use App\Http\Requests\Backend\SubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionPlanController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|show'                                    => 'subscription-list',
            'create|store|edit|update|destroy|toggleStatus' => 'subscription-manage',
        ];
    }

    public function index(Request $request): View
    {
        $cycleFilter = $request->get('billing_cycle');

        $plans = SubscriptionPlan::query()
            ->with('prices')
            ->withCount('subscriptions')
            ->withCount('activeSubscriptions')
            ->when($cycleFilter, fn ($q) => $q->byBillingCycle($cycleFilter))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $stats = [
            'total_plans'       => SubscriptionPlan::query()->count(),
            'active_plans'      => SubscriptionPlan::query()->where('status', true)->count(),
            'total_subscribers' => UserSubscription::query()->whereIn('status', ['active', 'trial', 'grace'])->count(),
            'total_revenue'     => SubscriptionTransaction::query()->where('type', '!=', 'refund')->sum('amount'),
        ];

        return view('backend.subscription.plans.index', compact('plans', 'stats', 'cycleFilter'));
    }

    public function create(): View
    {
        return view('backend.subscription.plans.create', $this->formData(new SubscriptionPlan));
    }

    public function store(SubscriptionPlanRequest $request): RedirectResponse
    {
        $plan = SubscriptionPlan::create($this->planData($request));
        $this->syncPrices($plan, (float) $request->input('price', 0), $request->filled('half_yearly_discount') ? (int) $request->input('half_yearly_discount') : null, $request->filled('yearly_discount') ? (int) $request->input('yearly_discount') : null);
        $this->syncFeatures($plan, $request->input('features', []));

        notifyEvs('success', __('Subscription plan created successfully.'));

        return redirect()->route('admin.subscription.plans.index');
    }

    public function edit(SubscriptionPlan $plan): View
    {
        return view('backend.subscription.plans.edit', $this->formData($plan->load('features', 'prices')));
    }

    public function update(SubscriptionPlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->planData($request));
        $this->syncPrices($plan, (float) $request->input('price', 0), $request->filled('half_yearly_discount') ? (int) $request->input('half_yearly_discount') : null, $request->filled('yearly_discount') ? (int) $request->input('yearly_discount') : null);
        $this->syncFeatures($plan, $request->input('features', []));

        notifyEvs('success', __('Subscription plan updated successfully.'));

        return redirect()->route('admin.subscription.plans.index');
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->activeSubscriptions()->exists()) {
            notifyEvs('error', __('Cannot delete a plan with active subscribers. Disable it instead.'));

            return redirect()->route('admin.subscription.plans.index');
        }

        $plan->delete();
        notifyEvs('success', __('Subscription plan deleted successfully.'));

        return redirect()->route('admin.subscription.plans.index');
    }

    public function toggleStatus(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['status' => ! $plan->status]);
        $label = $plan->status ? __('enabled') : __('disabled');
        notifyEvs('success', __('Plan :label successfully.', ['label' => $label]));

        return redirect()->route('admin.subscription.plans.index');
    }

    private function formData(SubscriptionPlan $plan): array
    {
        $prices          = $plan->prices->keyBy(fn ($p) => $p->billing_cycle instanceof BillingCycle ? $p->billing_cycle->value : $p->billing_cycle);
        $monthlyPrice    = $prices[BillingCycle::Monthly->value]?->price ?? 0;
        $halfYearlyPrice = $prices[BillingCycle::HalfYearly->value]      ?? null;
        $yearlyPrice     = $prices[BillingCycle::Yearly->value]          ?? null;

        return [
            'plan'               => $plan,
            'monthlyPrice'       => old('price', $monthlyPrice),
            'halfYearlyDiscount' => old('half_yearly_discount', $halfYearlyPrice?->discount),
            'yearlyDiscount'     => old('yearly_discount', $yearlyPrice?->discount),
            'featureTypes'       => [
                'limit'  => __('Limit (hard cap)'),
                'toggle' => __('Toggle (on/off)'),
                'quota'  => __('Quota (resets periodically)'),
            ],
            'resetCycles' => [
                ''        => __('No reset'),
                'daily'   => __('Daily'),
                'weekly'  => __('Weekly'),
                'monthly' => __('Monthly'),
            ],
        ];
    }

    private function planData(SubscriptionPlanRequest $request): array
    {
        $data = $request->safe()->except(['features', 'price', 'half_yearly_discount', 'yearly_discount']);

        $data['slug']               = $data['slug'] ?? Str::slug($data['name']);
        $data['is_featured']        = $request->boolean('is_featured');
        $data['auto_renew_default'] = $request->boolean('auto_renew_default');
        $data['status']             = $request->boolean('status');
        $data['trial_days']         = (int) ($data['trial_days'] ?? 0);
        $data['grace_days']         = (int) ($data['grace_days'] ?? 0);
        $data['sort_order']         = (int) ($data['sort_order'] ?? SubscriptionPlan::query()->max('sort_order') + 1);
        $data['plan_badge']         = trim((string) ($data['plan_badge'] ?? '')) ?: null;

        return $data;
    }

    private function syncPrices(SubscriptionPlan $plan, float $basePrice, ?int $halfYearlyDiscount, ?int $yearlyDiscount): void
    {
        // Monthly is always stored at the base price
        $plan->prices()->updateOrCreate(
            ['billing_cycle' => BillingCycle::Monthly->value],
            ['price' => $basePrice, 'discount' => null]
        );

        // Half Yearly: 6 months * base price, minus discount if provided
        $halfYearlyPrice = $basePrice * 6;
        if ($halfYearlyDiscount !== null) {
            $halfYearlyPrice = $halfYearlyPrice * (1 - $halfYearlyDiscount / 100);
        }
        $plan->prices()->updateOrCreate(
            ['billing_cycle' => BillingCycle::HalfYearly->value],
            ['price' => round($halfYearlyPrice, 2), 'discount' => $halfYearlyDiscount]
        );

        // Yearly: 12 months * base price, minus discount if provided
        $yearlyPrice = $basePrice * 12;
        if ($yearlyDiscount !== null) {
            $yearlyPrice = $yearlyPrice * (1 - $yearlyDiscount / 100);
        }
        $plan->prices()->updateOrCreate(
            ['billing_cycle' => BillingCycle::Yearly->value],
            ['price' => round($yearlyPrice, 2), 'discount' => $yearlyDiscount]
        );

        // Remove any legacy cycles (daily, weekly, lifetime) if present
        $plan->prices()->whereNotIn('billing_cycle', [
            BillingCycle::Monthly->value,
            BillingCycle::HalfYearly->value,
            BillingCycle::Yearly->value,
        ])->delete();
    }

    private function syncFeatures(SubscriptionPlan $plan, array $features): void
    {
        $incomingKeys = collect($features)->pluck('feature_key')->filter()->values();
        $plan->features()->whereNotIn('feature_key', $incomingKeys)->delete();

        foreach ($features as $index => $featureData) {
            if (empty($featureData['feature_key'])) {
                continue;
            }

            $plan->features()->updateOrCreate(
                ['feature_key' => $featureData['feature_key']],
                [
                    'feature_label' => $featureData['feature_label'] ?? $featureData['feature_key'],
                    'feature_value' => $featureData['feature_value'] ?? '1',
                    'feature_type'  => $featureData['feature_type']  ?? 'limit',
                    'reset_cycle'   => $featureData['reset_cycle'] ?: null,
                    'sort_order'    => (int) ($featureData['sort_order'] ?? $index),
                ]
            );
        }
    }
}
