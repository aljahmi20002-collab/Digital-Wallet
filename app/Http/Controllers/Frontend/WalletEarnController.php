<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\WalletEarnStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletEarn\StoreWalletEarnStakeRequest;
use App\Models\Wallet;
use App\Models\WalletEarnPlan;
use App\Models\WalletEarnReward;
use App\Models\WalletEarnStake;
use App\Services\WalletEarnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletEarnController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('user.wallet-earn.plans');
    }

    public function plans(): View
    {
        $user        = auth()->user();
        $wallets     = $user->activeWallets();
        $currencyIds = $wallets->pluck('currency_id')->all();

        $plans = WalletEarnPlan::query()
            ->with('currency')
            ->active()
            ->where(function ($query) use ($currencyIds) {
                $query->whereNull('currency_id')
                    ->orWhereIn('currency_id', $currencyIds);
            })
            ->orderByDesc('is_featured')
            ->orderByRaw("CASE WHEN plan_badge IS NOT NULL AND plan_badge <> '' THEN 1 ELSE 0 END DESC")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $metrics = $this->metrics((int) $user->id);

        return view('frontend.user.wallet_earn.plans', compact('plans', 'wallets', 'metrics'));
    }

    public function stakes(Request $request): View
    {
        $user   = auth()->user();
        $status = $request->get('status');

        $validStatuses = array_map(fn ($c) => $c->value, WalletEarnStatus::cases());

        $stakes = WalletEarnStake::query()
            ->with(['currency', 'plan', 'rewards' => fn ($q) => $q->orderByDesc('payout_number')->limit(5)])
            ->where('user_id', $user->id)
            ->when(in_array($status, $validStatuses), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->appends($request->only('status'));

        $metrics = $this->metrics((int) $user->id);

        return view('frontend.user.wallet_earn.stakes', compact('stakes', 'metrics', 'status'));
    }

    /**
     * @return array{active_amount: mixed, active_count: int, pending_count: int, completed_count: int, total_count: int, rewards_paid: mixed, next_payout_at: mixed}
     */
    private function metrics(int $userId): array
    {
        $counts = WalletEarnStake::query()
            ->where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'active_amount'   => WalletEarnStake::query()->where('user_id', $userId)->where('status', WalletEarnStatus::Active->value)->sum('principal_amount'),
            'active_count'    => (int) ($counts[WalletEarnStatus::Active->value] ?? 0),
            'pending_count'   => (int) ($counts[WalletEarnStatus::Pending->value] ?? 0),
            'completed_count' => (int) ($counts[WalletEarnStatus::Completed->value] ?? 0),
            'total_count'     => (int) $counts->sum(),
            'rewards_paid'    => WalletEarnReward::query()->where('user_id', $userId)->sum('amount'),
            'next_payout_at'  => WalletEarnStake::query()->where('user_id', $userId)->where('status', WalletEarnStatus::Active->value)->whereNotNull('next_payout_at')->min('next_payout_at'),
        ];
    }

    public function store(StoreWalletEarnStakeRequest $request, WalletEarnService $walletEarn): RedirectResponse
    {
        $plan   = WalletEarnPlan::query()->findOrFail($request->integer('plan_id'));
        $wallet = Wallet::query()->findOrFail($request->integer('wallet_id'));

        $walletEarn->createStake(auth()->user(), $plan, $wallet, (float) $request->input('amount'));

        notifyEvs('success', $plan->auto_approve
            ? __('Your Wallet Earn stake is now active.')
            : __('Your Wallet Earn stake is pending admin approval.')
        );

        return redirect()->route('user.wallet-earn.stakes');
    }

    public function show(WalletEarnStake $stake): View
    {
        abort_unless((int) $stake->user_id === auth()->id(), 404);

        $stake->load(['currency', 'wallet.currency', 'plan', 'rewards.transaction']);

        return view('frontend.user.wallet_earn.show', compact('stake'));
    }
}
