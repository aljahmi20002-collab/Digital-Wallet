<?php

namespace App\Http\Controllers\Frontend\Auth\Agent;

use App\Http\Controllers\Controller;
use App\Services\FeatureManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordResetLinkController extends Controller
{
    public function __construct(private readonly FeatureManager $features) {}

    public function create(): View
    {
        $this->ensureFeatureEnabled();

        return view(themedView('frontend.auth.agent.forgot-password'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureFeatureEnabled();

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

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('agent.password.reset', [
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

    private function ensureFeatureEnabled(): void
    {
        $byFeatureManager = $this->features->isEnabled('agent_program');
        $bySetting        = (bool) setting('agent_program_enabled', true);

        if (! $byFeatureManager || ! $bySetting) {
            throw new NotFoundHttpException;
        }
    }
}
