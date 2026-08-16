<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutOtherSessionsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $latestLoginActivityIdsByIp = $user->loginActivities()
            ->selectRaw('MAX(id)')
            ->groupBy('ip_address');

        $recentLoginActivities = $user->loginActivities()
            ->whereIn('id', $latestLoginActivityIdsByIp)
            ->latest('login_at')
            ->paginate(5)
            ->withQueryString();

        $securityChecks = collect([
            [
                'label'        => __('Email Verification'),
                'description'  => __('Keep recovery links and wallet alerts tied to a verified email address.'),
                'complete'     => $user->hasVerifiedEmail(),
                'icon'         => 'mail-check',
                'action_label' => __('Verify Email'),
                'action_url'   => $user->hasVerifiedEmail() ? null : route('user.settings.verify-email'),
            ],
            [
                'label'        => __('Phone Verification'),
                'description'  => __('Confirm your mobile number before recharge and SMS-protected wallet actions.'),
                'complete'     => $user->hasEnabledPhoneVerification(),
                'icon'         => 'phone-verification',
                'action_label' => $user->hasEnabledPhoneVerification() ? __('Enabled') : __('Enable Phone'),
                'action_url'   => $user->hasEnabledPhoneVerification() ? null : route('user.settings.phone.verify'),
            ],
            [
                'label'        => __('Authenticator 2FA'),
                'description'  => __('Require a one-time authenticator code after password sign-in.'),
                'complete'     => (bool) $user->two_factor_enabled,
                'icon'         => 'shield',
                'action_label' => (bool) $user->two_factor_enabled ? __('Manage 2FA') : __('Enable 2FA'),
                'action_url'   => route('user.settings.2fa.setup'),
            ],
            [
                'label'        => __('Wallet PIN'),
                'description'  => __('Authorize wallet payments with a separate private 6-digit PIN.'),
                'complete'     => $user->hasWalletPin(),
                'icon'         => 'wallet-cog',
                'action_label' => $user->hasWalletPin() ? __('Manage PIN') : __('Set PIN'),
                'action_url'   => route('user.settings.wallet-pin'),
            ],
        ]);

        $securityScore = (int) round(
            ($securityChecks->where('complete', true)->count() / $securityChecks->count()) * 100
        );

        $nextSecurityCheck = $securityChecks->firstWhere('complete', false);

        $knownIpCount = $user->loginActivities()
            ->distinct('ip_address')
            ->count('ip_address');

        return view('frontend.user.setting.security', [
            'currentIpAddress'      => $request->ip(),
            'knownIpCount'          => $knownIpCount,
            'nextSecurityCheck'     => $nextSecurityCheck,
            'recentLoginActivities' => $recentLoginActivities,
            'securityChecks'        => $securityChecks,
            'securityScore'         => $securityScore,
        ]);
    }

    public function logoutOtherSessions(LogoutOtherSessionsRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Demo-mode lockdown: seeded sandbox accounts share their
        // session across many concurrent evaluators — letting one of
        // them sign everyone else out (or worse, change credentials
        // through the same form) would break the shared demo.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('This action is disabled for the shared demo account.'));

            return redirect()->back();
        }

        Auth::logoutOtherDevices($request->validated('password'));

        $request->session()->put('password_hash_web', $user->getAuthPassword());

        Log::info('User signed out other browser sessions', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        notifyEvs('success', __('Other browser sessions have been signed out. Your current session is still active.'));

        return redirect()->route('user.settings.security.index');
    }
}
