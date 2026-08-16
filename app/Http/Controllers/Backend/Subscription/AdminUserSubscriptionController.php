<?php

namespace App\Http\Controllers\Backend\Subscription;

use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Backend\BaseController;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserSubscriptionController extends BaseController
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public static function permissions(): array
    {
        return [
            'index|show|transactions' => 'subscription-list',
            'activate|cancel'         => 'subscription-manage',
        ];
    }

    public function index(Request $request): View
    {
        $query = UserSubscription::query()
            ->with(['user', 'plan'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('plan_id')) {
            $query->where('subscription_plan_id', $request->input('plan_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $plans         = SubscriptionPlan::query()->orderBy('name')->get();

        return view('backend.subscription.user_subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(UserSubscription $subscription): View
    {
        $subscription->load([
            'user',
            'plan.features',
            'transactions' => fn ($query) => $query->latest(),
        ]);

        return view('backend.subscription.user_subscriptions.show', compact('subscription'));
    }

    public function activate(UserSubscription $subscription): RedirectResponse
    {
        try {
            $this->subscriptionService->adminActivate($subscription);
            notifyEvs('success', __('Subscription activated successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.subscription.user-subscriptions.show', $subscription);
    }

    public function cancel(UserSubscription $subscription): RedirectResponse
    {
        try {
            $this->subscriptionService->cancel($subscription, byAdmin: true);
            notifyEvs('success', __('Subscription cancelled successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.subscription.user-subscriptions.show', $subscription);
    }

    public function transactions(Request $request): View
    {
        $query = SubscriptionTransaction::query()
            ->with(['user', 'plan', 'subscription'])
            ->latest();

        if ($request->filled('plan_id')) {
            $query->where('subscription_plan_id', $request->input('plan_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20)->withQueryString();
        $plans        = SubscriptionPlan::query()->orderBy('name')->get();

        return view('backend.subscription.transactions.index', compact('transactions', 'plans'));
    }
}
