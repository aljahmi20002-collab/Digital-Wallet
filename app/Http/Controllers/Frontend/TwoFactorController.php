<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the 2FA setup form for users.
     *
     * Demo-mode note: this is a GET but it lazily *seeds* a 2FA secret on
     * first visit. For the shared demo accounts we still render the QR/secret
     * (so the screen looks complete) but we never persist a new secret —
     * persistence would lock the next evaluator out of the published
     * credentials. The enable/disable actions further down also no-op.
     */
    public function showSetupForm()
    {
        $user = Auth::user();

        if (! $user->google2fa_secret) {
            $generated = $this->twoFactorService->generateSecret();

            if (isDemoProtectedAccount($user->email)) {
                $user->google2fa_secret = $generated;
            } else {
                $user->google2fa_secret = $generated;
                $user->save();
            }
        }

        $qrCode = $this->twoFactorService->generateQrCode($user->google2fa_secret, $user->email);
        $secret = $user->google2fa_secret;

        return view('frontend.user.setting.2fa_security', compact('qrCode', 'secret'));
    }

    /**
     * Enable 2FA for the user after verifying the code.
     *
     * @throws NotifyErrorException
     */
    public function enable2fa(Request $request)
    {
        $user = Auth::user();

        // Demo-mode lockdown: seeded sandbox accounts cannot bind a 2FA
        // device, otherwise the next evaluator gets stopped at the
        // authenticator challenge with no way to recover the code.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Two-Factor changes are disabled for the shared demo account.'));

            return redirect()->route('user.settings.2fa.setup');
        }

        $validate = $request->validate([
            'verification_code' => 'required|digits:6',
        ]);

        $code = $validate['verification_code'];

        if ($this->twoFactorService->verifyCode($user->google2fa_secret, $code)) {
            $user->two_factor_enabled = true;
            $user->save();

            notifyEvs('success', 'Two-Factor Authentication enabled for user.');

            return redirect()->route('user.settings.2fa.setup');
        }

        throw new NotifyErrorException('Invalid verification code.');
    }

    /**
     * Disable Two-Factor Authentication.
     *
     * This method requires the user to confirm their password.
     *
     * @return RedirectResponse
     */
    public function disable2fa(Request $request)
    {
        $user = Auth::user();

        // Demo-mode lockdown: matches the enable side.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Two-Factor changes are disabled for the shared demo account.'));

            return redirect()->route('user.settings.2fa.setup');
        }

        // Validate the user's password using Laravel "current_password" rule
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        // Disable 2FA by clearing the secret and setting the flag to false.
        // Depending on your security needs, you may choose to keep the secret for re-enabling 2FA.
        $user->two_factor_enabled = false;
        $user->google2fa_secret   = null;
        $user->save();

        notifyEvs('success', __('Two-Factor Authentication has been disabled successfully.'));

        return redirect()->route('user.settings.2fa.setup');
    }
}
