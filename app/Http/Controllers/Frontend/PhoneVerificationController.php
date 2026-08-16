<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneVerification\DisablePhoneVerificationRequest;
use App\Http\Requests\PhoneVerification\SendPhoneVerificationCodeRequest;
use App\Http\Requests\PhoneVerification\VerifyPhoneRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PhoneVerificationController extends Controller
{
    public function show(): View
    {
        return view('frontend.user.setting.phone_verification', [
            'user' => auth()->user(),
        ]);
    }

    public function send(SendPhoneVerificationCodeRequest $request, PhoneVerificationService $service): RedirectResponse
    {
        // Demo-mode lockdown: sending real SMS to the seeded fictitious
        // phone numbers would either fail or — worse — spam someone.
        if (isDemoProtectedAccount($request->user()->email)) {
            notifyEvs('error', __('Phone verification is disabled for the shared demo account.'));

            return redirect()->route('user.settings.phone.verify');
        }

        $service->send($request->user());

        notifyEvs('success', __('A verification code has been sent to your phone number.'));

        return redirect()->route('user.settings.phone.verify');
    }

    public function verify(VerifyPhoneRequest $request, PhoneVerificationService $service): RedirectResponse
    {
        if (isDemoProtectedAccount($request->user()->email)) {
            notifyEvs('error', __('Phone verification is disabled for the shared demo account.'));

            return redirect()->route('user.settings.phone.verify');
        }

        $service->verify($request->user(), (string) $request->validated('code'));

        notifyEvs('success', __('Phone verification enabled successfully.'));

        return redirect()->route('user.settings.phone.verify');
    }

    public function disable(DisablePhoneVerificationRequest $request): RedirectResponse
    {
        if (isDemoProtectedAccount($request->user()->email)) {
            notifyEvs('error', __('Phone verification is disabled for the shared demo account.'));

            return redirect()->route('user.settings.phone.verify');
        }

        $request->user()->forceFill(['phone_verification_enabled' => false])->save();

        notifyEvs('success', __('Phone verification has been disabled.'));

        return redirect()->route('user.settings.phone.verify');
    }
}
