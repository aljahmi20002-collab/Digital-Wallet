<?php

namespace App\Http\Controllers\Frontend\Auth\Agent;

use App\Http\Controllers\Controller;
use App\Services\FeatureManager;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewPasswordController extends Controller
{
    public function __construct(private readonly FeatureManager $features) {}

    public function create(Request $request): View
    {
        $this->ensureFeatureEnabled();

        return view(themedView('frontend.auth.agent.reset-password'), ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureFeatureEnabled();

        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Belt-and-braces demo lockdown: PasswordResetLinkController
        // already refuses to mail a reset link for demo emails, but if
        // a stale or smuggled token reaches this endpoint we still
        // refuse to rewrite the password.
        if (isDemoProtectedAccount($request->input('email'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Password resets are disabled for the shared demo account.')]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        notifyEvs('success', __('Password updated successfully'));

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('agent.login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    private function ensureFeatureEnabled(): void
    {
        $byFeatureManager = $this->features->isEnabled('agent_program');
        $bySetting        = (bool) setting('agent_program_enabled', true);

        if (! $byFeatureManager || ! $bySetting) {
            throw new NotFoundHttpException;
        }
    }
}
