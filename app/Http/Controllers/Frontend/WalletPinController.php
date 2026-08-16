<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletPinUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class WalletPinController extends Controller
{
    /**
     * Show the wallet PIN setup or change form.
     */
    public function form(): View
    {
        return view('frontend.user.setting.wallet_pin', [
            'hasPin' => auth()->user()->hasWalletPin(),
        ]);
    }

    /**
     * Set or change the user's wallet PIN.
     */
    public function update(WalletPinUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Demo-mode lockdown: rotating the wallet PIN on a shared demo
        // account would block every subsequent evaluator from approving
        // wallet payments with the published PIN.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Wallet PIN changes are disabled for the shared demo account.'));

            return redirect()->route('user.settings.wallet-pin');
        }

        $user->wallet_pin = $request->validated('pin');
        $user->save();

        Log::info('Wallet PIN updated', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        notifyEvs('success', __('Wallet PIN updated successfully.'));

        return redirect()->route('user.settings.wallet-pin');
    }

    /**
     * Send an email link the user can use to clear their wallet PIN.
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Demo-mode lockdown: the reset link would clear the PIN for the
        // next evaluator and also spam the published fixture inbox.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Wallet PIN resets are disabled for the shared demo account.'));

            return redirect()->route('user.settings.wallet-pin');
        }

        if (! $user->email_verified_at) {
            notifyEvs('error', __('Verify your email address before resetting the wallet PIN.'));

            return redirect()->route('user.settings.wallet-pin');
        }

        $signedUrl = URL::temporarySignedRoute(
            'user.settings.wallet-pin.reset.confirm',
            now()->addMinutes(30),
            ['user' => $user->id]
        );

        Mail::raw(
            __('Click the link below within 30 minutes to clear your wallet PIN, then set a new one: :url', ['url' => $signedUrl]),
            function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject(__('Reset your wallet PIN'));
            }
        );

        Log::info('Wallet PIN reset link sent', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        notifyEvs('success', __('Wallet PIN reset link sent to your email.'));

        return redirect()->route('user.settings.wallet-pin');
    }

    /**
     * Confirm the email reset link and clear the wallet PIN.
     */
    public function confirmReset(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, __('Invalid or expired link.'));
        }

        if ($request->user()->id !== $user->id) {
            abort(403, __('This reset link is not for your account.'));
        }

        // Defense in depth: even if a signed link somehow reaches this
        // endpoint for a demo account, refuse to clear the shared PIN.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Wallet PIN resets are disabled for the shared demo account.'));

            return redirect()->route('user.settings.wallet-pin');
        }

        $user->wallet_pin = null;
        $user->save();

        Log::info('Wallet PIN cleared via email reset link', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        notifyEvs('success', __('Wallet PIN cleared. Set a new PIN to continue using wallet payments.'));

        return redirect()->route('user.settings.wallet-pin');
    }
}
