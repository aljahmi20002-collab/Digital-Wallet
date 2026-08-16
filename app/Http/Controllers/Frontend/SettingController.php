<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Gender;
use App\Enums\WalletEarnStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Traits\FileManageTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class SettingController extends Controller
{
    use FileManageTrait;

    public function profile()
    {
        $user = auth()->user();

        return view('frontend.user.setting.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();

        // In demo mode the seeded sandbox accounts (users/merchants/agents
        // listed in the public CodeCanyon demo credentials) must stay
        // logged-in-able for every visitor — so they cannot rotate their
        // own email. Anything outside the demo allowlist is unaffected.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Profile updates are disabled for the shared demo account.'));

            return redirect()->back();
        }

        // validation rules
        $validate = $request->validate([
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'first_name'       => 'nullable',
            'last_name'        => 'nullable',
            'business_name'    => 'nullable',
            'business_address' => 'nullable',
            'username'         => 'required|unique:users,username,'.$user->id,
            'gender'           => ['required', new Enum(Gender::class)],
            'birthday'         => 'nullable|date',
            'phone'            => 'nullable',
            'country'          => 'nullable',
            'state'            => 'nullable',
            'city'             => 'nullable',
            'postal_code'      => 'nullable',
            'address'          => 'nullable',
            'email'            => 'required|unique:users,email,'.$user->id,
        ]);

        // if user uploaded a new avatar, update the avatar
        if ($request->hasFile('avatar')) {
            $validate['avatar'] = $this->uploadImage($request->file('avatar'), $user->avatar);
        }

        if ($user->email !== $validate['email']) {
            $validate['email_verified_at'] = null;
        }

        if ((string) $user->phone !== (string) ($validate['phone'] ?? '')) {
            $validate['phone_verified_at']          = null;
            $validate['phone_verification_enabled'] = false;
        }

        // update the user
        $user->update($validate);

        notifyEvs('success', 'Profile updated successfully');

        // return the user back to the form with a success message
        return redirect()->back();
    }

    public function verifyEmail()
    {

        if (auth()->user()->hasVerifiedEmail()) {
            notifyEvs('warning', 'Your email address is already verified');

            return redirect()->intended(route('user.settings.profile'));
        }

        auth()->user()->sendEmailVerificationNotification();
        notifyEvs('success', 'A fresh verification link has been sent to your email addres');

        // return the user back to the form with an error message
        return redirect()->back();
    }

    public function changePassword(): View
    {
        return view('frontend.user.setting.change_password');
    }

    public function subscriptionStatus(Request $request): View
    {
        $subscription = $request->user()
            ->activeSubscription()
            ->with(['plan.features', 'plan.prices'])
            ->first();

        return view('frontend.user.setting.subscription_status', compact('subscription'));
    }

    public function walletEarnStatus(Request $request): View
    {
        $activeStakes = $request->user()
            ->walletEarnStakes()
            ->with(['currency', 'plan'])
            ->where('status', WalletEarnStatus::Active)
            ->latest('starts_at')
            ->latest()
            ->get();

        $walletEarnSummary = [
            'active_count'     => $activeStakes->count(),
            'principal_amount' => (float) $activeStakes->sum('principal_amount'),
            'expected_profit'  => (float) $activeStakes->sum('expected_profit'),
            'paid_profit'      => (float) $activeStakes->sum('paid_profit'),
            'next_payout_at'   => $activeStakes
                ->pluck('next_payout_at')
                ->filter()
                ->sort()
                ->first(),
        ];

        return view('frontend.user.setting.wallet_earn_status', compact('activeStakes', 'walletEarnSummary'));
    }

    public function passwordUpdate(UpdateUserPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Demo-mode lockdown: seeded sandbox accounts may not rotate
        // their password, otherwise the next evaluator who tries to
        // log in with the publicly listed credentials gets locked out.
        if (isDemoProtectedAccount($user->email)) {
            notifyEvs('error', __('Password changes are disabled for the shared demo account.'));

            return redirect()->back();
        }

        $validated = $request->validated();

        Auth::logoutOtherDevices($validated['old_password']);

        $user->password = Hash::make($validated['password']);
        $user->save();

        $request->session()->put('password_hash_web', $user->getAuthPassword());

        notifyEvs('success', 'Password updated successfully');

        return redirect()->back();
    }
}
