<?php

namespace App\Http\Controllers\Frontend\Auth\Agent;

use App\Enums\UserRole;
use App\Events\TransactionUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AgentRegisterRequest;
use App\Models\Referral;
use App\Models\User;
use App\Models\UserFeature;
use App\Services\AgentService;
use App\Services\FeatureManager;
use Cookie;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly FeatureManager $features,
        private readonly AgentService $agents,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $this->ensureFeatureEnabled();
        $this->ensureSelfRegistrationAllowed();

        return view(themedView('frontend.auth.agent.register'));
    }

    public function store(AgentRegisterRequest $request): RedirectResponse
    {
        $this->ensureFeatureEnabled();
        $this->ensureSelfRegistrationAllowed();

        $validated      = $request->validated();
        $countryCode    = $request->countryCode();
        $formattedPhone = $request->formattedPhone();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'username'   => $validated['username'],
            'email'      => $validated['email'],
            'country'    => $countryCode,
            'phone'      => $formattedPhone,
            'role'       => UserRole::AGENT,
            'password'   => Hash::make($validated['password']),
        ]);

        if ($referralCode = Cookie::get('referral_code')) {
            $referrer = User::where('referral_code', $referralCode)->first();

            if ($referrer) {
                $parentReferral = Referral::where('referred_user_id', $referrer->id)->first();

                Referral::create([
                    'user_id'            => $referrer->id,
                    'referred_user_id'   => $user->id,
                    'parent_referral_id' => optional($parentReferral)->id,
                ]);
            }
        }

        event(new Registered($user));
        event(new TransactionUpdated($user));
        UserFeature::syncWithConfigForUser($user->id);
        $this->agents->createDefaultForUser($user);
        Auth::login($user);

        notifyEvs('success', __('Agent registration successful.'));

        return redirect()->route('user.dashboard');
    }

    private function ensureFeatureEnabled(): void
    {
        $byFeatureManager = $this->features->isEnabled('agent_program');
        $bySetting        = (bool) setting('agent_program_enabled', true);

        if (! $byFeatureManager || ! $bySetting) {
            throw new NotFoundHttpException;
        }
    }

    private function ensureSelfRegistrationAllowed(): void
    {
        if (! (bool) setting('agent_self_registration', true)) {
            throw new NotFoundHttpException;
        }
    }
}
