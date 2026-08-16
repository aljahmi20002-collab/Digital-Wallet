<?php

namespace App\Http\Controllers\Frontend\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view(themedView('frontend.auth.user.forgot-password'));
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Demo-mode lockdown: shared demo logins must never be reset
        // via the forgot-password flow either, otherwise an evaluator
        // could mail themselves a reset link and lock everyone else out.
        if (isDemoProtectedAccount($request->input('email'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Password resets are disabled for the shared demo account.')]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('user.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        $status = Password::broker('users')->sendResetLink($request->only('email'));

        notifyEvs('success', __('Reset Link Sent Successfully'));

        return $status === Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
