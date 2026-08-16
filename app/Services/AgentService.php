<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\Currency;
use App\Models\User;
use App\Notifications\TemplateNotification;
use App\Traits\FileManageTrait;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Spatie\Permission\Models\Permission;

class AgentService
{
    use FileManageTrait;

    /**
     * Create a new agent application for the given user from validated input.
     *
     * @param array<string, mixed> $validated
     */
    public function createForUser(User $user, array $validated, mixed $logoFile = null): Agent
    {
        $currencyIds            = $this->currencyIdsFrom($validated);
        $payload                = collect($validated)->except('currency_ids')->all();
        $payload['user_id']     = $user->id;
        $payload['currency_id'] = $currencyIds[0];

        if ($logoFile) {
            $payload['logo'] = $this->uploadImage($logoFile);
        }

        $agent = Agent::create($payload);
        $this->syncSupportedCurrencies($agent, $currencyIds);

        $this->notifyAdminsAboutRequest($user, $agent);

        return $agent;
    }

    /**
     * Create the minimum agent profile required for agent listings and access.
     */
    public function createDefaultForUser(User $user, ?string $status = null): Agent
    {
        $currency = Currency::getDefault() ?? Currency::query()->where('status', true)->first();

        if (! $currency) {
            throw new RuntimeException('No active currency is available for agent profile creation.');
        }

        $agent = Agent::create([
            'user_id'     => $user->id,
            'currency_id' => $currency->id,
            'agent_name'  => $this->defaultAgentName($user),
            'status'      => $status,
        ]);

        $this->syncSupportedCurrencies($agent, [(int) $currency->id]);
        $this->notifyAdminsAboutRequest($user, $agent);

        return $agent;
    }

    /**
     * Update an existing agent's editable fields.
     *
     * @param array<string, mixed> $validated
     */
    public function updateAgent(Agent $agent, array $validated, mixed $logoFile = null): Agent
    {
        $currencyIds            = $this->currencyIdsFrom($validated);
        $payload                = collect($validated)->except('currency_ids')->all();
        $payload['currency_id'] = $currencyIds[0];

        if ($logoFile) {
            $payload['logo'] = $this->uploadImage($logoFile, $agent->getRawOriginal('logo'));
        }

        $agent->update($payload);
        $this->syncSupportedCurrencies($agent, $currencyIds);

        return $agent->refresh();
    }

    /**
     * @param  array<string, mixed> $validated
     * @return array<int, int>
     */
    private function currencyIdsFrom(array $validated): array
    {
        return collect($validated['currency_ids'] ?? [$validated['currency_id'] ?? null])
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->tap(function ($currencyIds): void {
                if ($currencyIds->isEmpty()) {
                    throw new RuntimeException('At least one supported currency is required for agent profile creation.');
                }
            })
            ->all();
    }

    /**
     * @param array<int, int> $currencyIds
     */
    private function syncSupportedCurrencies(Agent $agent, array $currencyIds): void
    {
        $primaryCurrencyId = $currencyIds[0] ?? (int) $agent->currency_id;

        $syncPayload = collect($currencyIds)
            ->mapWithKeys(fn (int $currencyId): array => [
                $currencyId => ['is_primary' => $currencyId === $primaryCurrencyId],
            ])
            ->all();

        $agent->supportedCurrencies()->sync($syncPayload);
    }

    /**
     * Notify admins that a new agent request has been submitted.
     */
    protected function notifyAdminsAboutRequest(User $user, Agent $agent): void
    {
        if (! (bool) setting('agent_admin_email_notify', true)) {
            return;
        }

        if (! Permission::query()->where('guard_name', 'admin')->where('name', 'agent-request-notification')->exists()) {
            return;
        }

        $admins = Admin::permission('agent-request-notification')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new TemplateNotification(
            identifier: 'agent_admin_notify_request',
            data: [
                'user'           => $user->name,
                'agent_name'     => $agent->agent_name,
                'agent_code'     => $agent->agent_code,
                'currencies'     => $this->supportedCurrencyCodes($agent),
                'operating_note' => $agent->description ?: __('Not provided'),
                'email'          => $user->email,
                'phone'          => $user->phone,
            ],
            sender: $user,
            action: route('admin.agent.pending')
        ));
    }

    protected function defaultAgentName(User $user): string
    {
        $name = trim($user->name);

        return $name !== '' ? $name : $user->username;
    }

    protected function supportedCurrencyCodes(Agent $agent): string
    {
        $agent->loadMissing('supportedCurrencies');

        return $agent->supportedCurrencies->pluck('code')->implode(', ') ?: (string) $agent->currency?->code;
    }
}
