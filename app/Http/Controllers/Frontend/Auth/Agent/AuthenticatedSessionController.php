<?php

namespace App\Http\Controllers\Frontend\Auth\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\FeatureManager;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly FeatureManager $features) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        $this->ensureFeatureEnabled();

        return view(themedView('frontend.auth.agent.login'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $this->ensureFeatureEnabled();

        $request->authenticate();

        $user = Auth::user();
        if ($user->role !== UserRole::AGENT) {
            Auth::logout();
            notifyEvs('error', __('Please use the regular login for non-agent accounts.'));

            return redirect()->route('user.login');
        }

        $request->session()->regenerate();

        app(WalletService::class)->createDefaultWalletForUser($user);

        return redirect()->intended(route('user.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
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
