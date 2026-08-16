<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\StoreAgentRequest;
use App\Http\Requests\Agent\UpdateAgentRequest;
use App\Models\Agent;
use App\Services\AgentOperationService;
use App\Services\AgentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AgentService $agents,
        private readonly AgentOperationService $agentOperations,
    ) {}

    public function index(): View
    {
        $this->ensureProgramEnabled();

        $agents = Agent::query()
            ->where('user_id', auth()->id())
            ->with(['currency', 'supportedCurrencies'])
            ->latest()
            ->get();

        $dashboard = $this->agentOperations->dashboard(auth()->user());

        return view('frontend.user.agent.index', compact('agents', 'dashboard'));
    }

    public function create(): View|RedirectResponse
    {
        $this->ensureProgramEnabled();

        if ($redirect = $this->guardCreate()) {
            return $redirect;
        }

        $currencies = auth()->user()->activeWallets()->pluck('currency')->unique('id')->values();

        return view('frontend.user.agent.create', compact('currencies'));
    }

    public function store(StoreAgentRequest $request): RedirectResponse
    {
        $this->ensureProgramEnabled();

        if ($redirect = $this->guardCreate()) {
            return $redirect;
        }

        $this->agents->createForUser(
            $request->user(),
            $request->validated(),
            $request->file('logo')
        );

        notifyEvs('success', __('New Agent Request Submitted Successfully'));

        return to_route('user.agent.index');
    }

    public function edit(Agent $agent): View|RedirectResponse
    {
        $this->ensureProgramEnabled();

        if ($agent->user_id !== auth()->id()) {
            abort(403);
        }

        if ($agent->isActionLocked()) {
            notifyEvs('error', __('This agent is disabled or rejected. Editing is not allowed.'));

            return to_route('user.agent.index');
        }

        $agent->loadMissing('supportedCurrencies');

        $currencies = auth()->user()->activeWallets()->pluck('currency')->unique('id')->values();

        return view('frontend.user.agent.edit', compact('agent', 'currencies'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $this->ensureProgramEnabled();

        if ($agent->user_id !== auth()->id()) {
            abort(403);
        }

        if ($agent->isActionLocked()) {
            notifyEvs('error', __('This agent is disabled or rejected. Updating is not allowed.'));

            return to_route('user.agent.index');
        }

        $this->agents->updateAgent($agent, $request->validated(), $request->file('logo'));

        notifyEvs('success', __('Agent details updated successfully'));

        return to_route('user.agent.index');
    }

    public function regenerateQr(Agent $agent): RedirectResponse
    {
        $this->ensureProgramEnabled();

        if ($agent->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $agent->isApproved()) {
            notifyEvs('error', __('Only approved agent accounts can regenerate QR codes.'));

            return to_route('user.agent.index', ['tab' => 'counter-cashout']);
        }

        $agent->regenerateQrToken();

        notifyEvs('success', __('Agent QR code regenerated successfully. Print the new QR before accepting QR cash-out.'));

        return to_route('user.agent.index', ['tab' => 'counter-cashout']);
    }

    /**
     * Master toggle. When admin disables the agent program from Settings,
     * every dashboard agent surface returns 404.
     */
    private function ensureProgramEnabled(): void
    {
        if (! (bool) setting('agent_program_enabled', true)) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * Settings-driven guards for creating a new agent profile.
     * Returns a RedirectResponse to abort, or null when allowed.
     */
    private function guardCreate(): ?RedirectResponse
    {
        $user = auth()->user();

        // KYC requirement
        if ((bool) setting('agent_require_kyc', false) && ! $user->isKycVerified()) {
            notifyEvs('error', __('You must complete KYC verification before applying as an agent.'));

            return to_route('user.agent.index');
        }

        // Country whitelist
        $allowedCountries = $this->parseAllowedCountries();
        $userCountry      = strtoupper((string) ($user->country ?? ''));
        if ($allowedCountries !== [] && ! in_array($userCountry, $allowedCountries, true)) {
            notifyEvs('error', __('The Agent program is not available in your country.'));

            return to_route('user.agent.index');
        }

        // One user owns one agent account. Existing pending, approved, disabled,
        // or rejected requests all count so users do not create duplicates.
        if (Agent::query()->where('user_id', $user->id)->exists()) {
            notifyEvs('error', __('You already have an agent account or request on this profile.'));

            return to_route('user.agent.index');
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function parseAllowedCountries(): array
    {
        $raw = (string) setting('agent_allowed_countries', '');
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values()
            ->all();
    }
}
