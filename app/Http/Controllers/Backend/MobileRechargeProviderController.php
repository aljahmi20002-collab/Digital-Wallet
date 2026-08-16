<?php

namespace App\Http\Controllers\Backend;

use App\Enums\FixPctType;
use App\Http\Requests\Backend\MobileRecharge\StoreMobileRechargeProviderRequest;
use App\Http\Requests\Backend\MobileRecharge\UpdateMobileRechargeProviderRequest;
use App\Models\MobileRechargeProvider;
use App\Models\Plugin;
use App\Models\Setting;
use App\Traits\FileManageTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Manage mobile recharge providers.
 *
 * Business rules (fee, limits, regions, default flag) live in the
 * mobile_recharge_providers table. Driver credentials (client_id, token,
 * etc.) live in the matching `plugins` row and open through the shared
 * plugin credential modal.
 */
class MobileRechargeProviderController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'create|store|edit|update|destroy' => 'mobile-recharge-manage',
        ];
    }

    public function create(Request $request): View
    {
        $view = $request->ajax()
            ? 'backend.mobile_recharge.providers._manage_append'
            : 'backend.mobile_recharge.providers.create';

        return view($view, [
            'driverLabels' => $this->driverLabels(),
            'provider'     => null,
        ]);
    }

    public function store(StoreMobileRechargeProviderRequest $request): RedirectResponse
    {
        $payload = $this->normalizePayload($this->validatedPayload($request));

        DB::transaction(function () use ($payload): void {
            if ($payload['provider']['is_default']) {
                $this->demoteOtherDefaults();
            }

            $plugin = Plugin::query()->create([
                'type'        => Plugin::TYPE_MOBILE_RECHARGE,
                'code'        => $payload['provider']['code'],
                'name'        => $payload['provider']['name'],
                'logo'        => $payload['provider']['logo'] ?: 'general/static/plugins/mobile-recharge.svg',
                'description' => $payload['provider']['description'] ?? '',
                'credentials' => json_encode($this->defaultCredentialsForDriver($payload['provider']['driver'])),
                'status'      => $payload['provider']['status'],
            ]);

            MobileRechargeProvider::query()->create(array_merge(
                $payload['provider'],
                ['plugin_id' => $plugin->id],
            ));

            if ($payload['provider']['is_default']) {
                Setting::set('mobile_recharge_provider', $payload['provider']['code'], 'string');
            }
        });

        notifyEvs('success', __('Mobile recharge provider added successfully.'));

        return $this->redirectToProvidersTab();
    }

    public function edit(Request $request, MobileRechargeProvider $provider): View
    {
        $provider->load('plugin');

        $view = $request->ajax()
            ? 'backend.mobile_recharge.providers._manage_append'
            : 'backend.mobile_recharge.providers.edit';

        return view($view, [
            'provider'     => $provider,
            'driverLabels' => $this->driverLabels(),
        ]);
    }

    public function update(UpdateMobileRechargeProviderRequest $request, MobileRechargeProvider $provider): RedirectResponse
    {
        $provider->load('plugin');
        $payload = $this->normalizePayload($this->validatedPayload($request, $provider), $provider);

        DB::transaction(function () use ($payload, $provider): void {
            if ($payload['provider']['is_default']) {
                $this->demoteOtherDefaults($provider->id);
            }

            $provider->update($payload['provider']);

            if ($provider->plugin) {
                $provider->plugin->update([
                    'name'        => $payload['provider']['name'],
                    'logo'        => $payload['provider']['logo'] ?: $provider->plugin->logo,
                    'description' => $payload['provider']['description'] ?? $provider->plugin->description,
                    'credentials' => json_encode($this->ensureCredentialTemplate(
                        $provider->plugin->credentialsArray(),
                        $payload['provider']['driver'],
                    )),
                    'status' => $payload['provider']['status'],
                ]);
            }

            if ($payload['provider']['is_default']) {
                Setting::set('mobile_recharge_provider', $payload['provider']['code'], 'string');
            }
        });

        notifyEvs('success', __('Mobile recharge provider updated successfully.'));

        return $this->redirectToProvidersTab();
    }

    public function destroy(MobileRechargeProvider $provider): RedirectResponse
    {
        if ($provider->recharges()->exists()) {
            notifyEvs('error', __('This provider has linked recharge history and cannot be deleted. Disable it instead.'));

            return redirect()->back();
        }

        DB::transaction(function () use ($provider): void {
            $provider->plugin?->delete();
            $provider->delete();
        });

        notifyEvs('success', __('Mobile recharge provider deleted.'));

        return $this->redirectToProvidersTab();
    }

    private function redirectToProvidersTab(): RedirectResponse
    {
        return redirect()->route('admin.mobile-recharge.index', ['tab' => 'providers']);
    }

    private function demoteOtherDefaults(?int $exceptId = null): void
    {
        MobileRechargeProvider::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    /**
     * @param  array<string, mixed>                  $data
     * @return array{provider: array<string, mixed>}
     */
    private function normalizePayload(array $data, ?MobileRechargeProvider $existing = null): array
    {
        $isDefault = (bool) ($data['is_default'] ?? false);
        $status    = $isDefault || (bool) $data['status'];
        $nextOrder = $existing?->order ?? ((int) MobileRechargeProvider::query()->max('order') + 1);
        $fee       = $this->normalizeFee($data, $existing);

        $providerRow = [
            'code'                 => $data['code'],
            'name'                 => $data['name'],
            'driver'               => $data['driver'],
            'logo'                 => $data['logo'] ?? $existing?->logo,
            'description'          => array_key_exists('description', $data) ? ($data['description'] ?? '') : ($existing?->description ?? ''),
            'status'               => $status,
            'is_default'           => $isDefault,
            'supported_countries'  => array_key_exists('supported_countries', $data) ? $this->cleanList($data['supported_countries'], true) : $existing?->supported_countries,
            'supported_currencies' => array_key_exists('supported_currencies', $data) ? $this->cleanList($data['supported_currencies'], true) : $existing?->supported_currencies,
            'fee_fixed'            => $fee['fixed'],
            'fee_percent'          => $fee['percent'],
            'min_amount'           => (float) ($data['min_amount'] ?? 0),
            'max_amount'           => $data['max_amount'] !== null && $data['max_amount'] !== '' ? (float) $data['max_amount'] : null,
            'order'                => $nextOrder,
            'config'               => $this->cleanConfig($data['config'] ?? [], $existing?->config ?? []),
        ];

        return [
            'provider' => $providerRow,
        ];
    }

    /**
     * @param  array<string, mixed>                $data
     * @return array{fixed: float, percent: float}
     */
    private function normalizeFee(array $data, ?MobileRechargeProvider $existing = null): array
    {
        if (array_key_exists('fee_amount', $data)) {
            $amount = (float) ($data['fee_amount'] ?? 0);
            $type   = (string) ($data['fee_type'] ?? FixPctType::FIXED->value);

            return [
                'fixed'   => $type === FixPctType::FIXED->value ? $amount : 0.0,
                'percent' => $type === FixPctType::PERCENT->value ? $amount : 0.0,
            ];
        }

        return [
            'fixed'   => (float) ($data['fee_fixed'] ?? $existing?->fee_fixed ?? 0),
            'percent' => (float) ($data['fee_percent'] ?? $existing?->fee_percent ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(StoreMobileRechargeProviderRequest|UpdateMobileRechargeProviderRequest $request, ?MobileRechargeProvider $provider = null): array
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploadImage($request->file('logo'), $provider?->logo);
        } elseif ($provider) {
            $data['logo'] = $provider->logo;
        } else {
            unset($data['logo']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>|null $submitted
     * @param  array<string, mixed>      $existing
     * @return array<string, mixed>
     */
    private function cleanConfig(?array $submitted, array $existing): array
    {
        if (! is_array($submitted)) {
            return $existing;
        }

        $merged = $existing;

        foreach ($submitted as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($value === '0' || $value === '1') {
                $merged[$key] = (bool) (int) $value;

                continue;
            }

            $merged[$key] = is_string($value) ? trim($value) : $value;
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultCredentialsForDriver(string $driver): array
    {
        return match ($driver) {
            'http' => [
                'base_url' => null,
                'endpoint' => '/recharges',
                'token'    => null,
                'timeout'  => 15,
            ],
            'reloadly' => [
                'client_id'     => null,
                'client_secret' => null,
                'sandbox'       => true,
                'timeout'       => 20,
            ],
            default => [
                'sandbox_status' => 'completed',
            ],
        };
    }

    /**
     * @param  array<string, mixed> $existing
     * @return array<string, mixed>
     */
    private function ensureCredentialTemplate(array $existing, string $driver): array
    {
        return array_merge($this->defaultCredentialsForDriver($driver), $existing);
    }

    /**
     * @param  array<int, string>|string|null $value
     * @return array<int, string>|null
     */
    private function cleanList(array|string|null $value, bool $upper = false): ?array
    {
        if ($value === null) {
            return null;
        }

        $items = is_array($value) ? $value : preg_split('/[,\s]+/', (string) $value);
        $items = array_values(array_filter(array_map('trim', $items ?: []), static fn ($item) => $item !== ''));

        if ($items === []) {
            return null;
        }

        if ($upper) {
            $items = array_map('strtoupper', $items);
        }

        return array_values(array_unique($items));
    }

    /**
     * @return array<string, string>
     */
    private function driverLabels(): array
    {
        return (array) config('mobile_services.recharge.driver_labels', []);
    }
}
