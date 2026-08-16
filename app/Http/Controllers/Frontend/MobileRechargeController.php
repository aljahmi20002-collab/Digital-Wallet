<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\MobileRechargeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobileRecharge\StoreMobileRechargeRequest;
use App\Models\Wallet;
use App\Services\MobileRecharge\MobileRechargeProviderManager;
use App\Services\MobileRechargeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MobileRechargeController extends Controller
{
    public function create(MobileRechargeProviderManager $providers): View
    {
        $user            = auth()->user();
        $wallets         = $user->activeWallets();
        $recentRecharges = $user->mobileRecharges()
            ->with(['wallet.currency'])
            ->latest()
            ->limit(10)
            ->get();

        $activeProvider = $providers->resolveActiveProvider();

        return view('frontend.user.mobile_recharge.create', compact('wallets', 'recentRecharges', 'activeProvider'));
    }

    public function store(StoreMobileRechargeRequest $request, MobileRechargeService $service): RedirectResponse
    {
        $validated = $request->validated();
        $user      = $request->user();

        $wallet = Wallet::query()
            ->where('user_id', $user->id)
            ->findOrFail($validated['wallet_id']);

        $recharge = $service->recharge(
            user: $user,
            wallet: $wallet,
            phoneNumber: $validated['phone_number'],
            amount: (float) $validated['amount'],
            operator: $validated['operator'] ?? null,
            country: $validated['country']   ?? null,
        );

        if ($recharge->status === MobileRechargeStatus::FAILED) {
            notifyEvs('error', $recharge->failure_reason ?: __('Mobile recharge failed and the wallet debit was refunded.'));

            return redirect()->back();
        }

        $message = $recharge->status === MobileRechargeStatus::COMPLETED
            ? __('Mobile recharge completed successfully.')
            : __('Mobile recharge submitted and is processing.');

        notifyEvs('success', $message);

        return redirect()->route('user.mobile-recharge.create');
    }
}
