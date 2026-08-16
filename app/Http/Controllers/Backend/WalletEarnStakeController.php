<?php

namespace App\Http\Controllers\Backend;

use App\Enums\WalletEarnStatus;
use App\Models\Currency;
use App\Models\WalletEarnPlan;
use App\Models\WalletEarnReward;
use App\Models\WalletEarnStake;
use App\Services\WalletEarnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletEarnStakeController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|show'                     => 'wallet-earn-list',
            'approve|reject|cancel|complete' => 'wallet-earn-manage',
        ];
    }

    public function index(Request $request): View
    {
        $stakes = WalletEarnStake::query()
            ->with(['user', 'currency', 'plan'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('currency_id'), fn ($query) => $query->where('currency_id', $request->integer('currency_id')))
            ->when($request->filled('plan_id'), fn ($query) => $query->where('wallet_earn_plan_id', $request->integer('plan_id')))
            ->when($request->filled('user'), function ($query) use ($request) {
                $search = $request->string('user')->toString();
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_staked' => WalletEarnStake::query()->whereIn('status', [
                WalletEarnStatus::Active->value,
                WalletEarnStatus::Completed->value,
            ])->sum('principal_amount'),
            'active'       => WalletEarnStake::query()->where('status', WalletEarnStatus::Active->value)->count(),
            'pending'      => WalletEarnStake::query()->where('status', WalletEarnStatus::Pending->value)->count(),
            'completed'    => WalletEarnStake::query()->where('status', WalletEarnStatus::Completed->value)->count(),
            'rewards_paid' => WalletEarnReward::query()->sum('amount'),
        ];

        return view('backend.wallet_earn.stakes.index', [
            'stakes'     => $stakes,
            'metrics'    => $metrics,
            'statuses'   => WalletEarnStatus::options(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'plans'      => WalletEarnPlan::query()->orderBy('name')->get(),
        ]);
    }

    public function show(WalletEarnStake $stake): View
    {
        $stake->load(['user', 'wallet.currency', 'currency', 'plan', 'rewards.transaction', 'reviewer']);

        return view('backend.wallet_earn.stakes.show', compact('stake'));
    }

    public function approve(Request $request, WalletEarnStake $stake, WalletEarnService $walletEarn): RedirectResponse
    {
        $walletEarn->approve($stake, auth()->user(), $request->string('review_note')->toString() ?: null);

        notifyEvs('success', __('Wallet Earn stake approved successfully.'));

        return redirect()->back();
    }

    public function reject(Request $request, WalletEarnStake $stake, WalletEarnService $walletEarn): RedirectResponse
    {
        $walletEarn->reject($stake, auth()->user(), $request->string('review_note')->toString() ?: null);

        notifyEvs('success', __('Wallet Earn stake rejected and principal returned.'));

        return redirect()->back();
    }

    public function cancel(Request $request, WalletEarnStake $stake, WalletEarnService $walletEarn): RedirectResponse
    {
        $walletEarn->cancel($stake, auth()->user(), $request->string('review_note')->toString() ?: null);

        notifyEvs('success', __('Wallet Earn stake canceled and principal returned.'));

        return redirect()->back();
    }

    public function complete(WalletEarnStake $stake, WalletEarnService $walletEarn): RedirectResponse
    {
        $walletEarn->complete($stake);

        notifyEvs('success', __('Wallet Earn stake completed successfully.'));

        return redirect()->back();
    }
}
