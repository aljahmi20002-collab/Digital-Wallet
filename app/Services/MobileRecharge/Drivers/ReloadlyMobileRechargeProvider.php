<?php

namespace App\Services\MobileRecharge\Drivers;

use App\Contracts\MobileRecharge\MobileRechargeProviderInterface;
use App\Enums\MobileRechargeStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\MobileRecharge;
use App\Models\MobileRechargeProvider;
use App\Services\MobileRecharge\MobileRechargeProviderResult;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Reloadly Airtime API driver.
 *
 * Reloadly is a global airtime/recharge aggregator covering 180+ countries
 * with sandbox + production environments. Auth uses OAuth2 client
 * credentials → bearer token (cached). Topup endpoint: POST /topups.
 * Credentials are read from the matching plugin row; non-sensitive
 * provider config (default country, use_local_amount, etc.) is read from
 * the mobile_recharge_provider config column.
 *
 * @link https://developers.reloadly.com/
 */
class ReloadlyMobileRechargeProvider implements MobileRechargeProviderInterface
{
    private const AUTH_URL = 'https://auth.reloadly.com/oauth/token';

    private const SANDBOX_AUDIENCE = 'https://topups-sandbox.reloadly.com';

    private const SANDBOX_BASE = 'https://topups-sandbox.reloadly.com';

    private const PRODUCTION_AUDIENCE = 'https://topups.reloadly.com';

    private const PRODUCTION_BASE = 'https://topups.reloadly.com';

    private const TOKEN_CACHE_PREFIX = 'mobile_recharge_reloadly_token:';

    private ?MobileRechargeProvider $provider = null;

    /** @var array<string, mixed> */
    private array $credentials = [];

    /** @var array<string, mixed> */
    private array $config = [];

    /**
     * @param array<string, mixed> $credentials
     * @param array<string, mixed> $config
     */
    public function configure(MobileRechargeProvider $provider, array $credentials, array $config): void
    {
        $this->provider    = $provider;
        $this->credentials = $credentials;
        $this->config      = $config;
    }

    public function recharge(MobileRecharge $recharge): MobileRechargeProviderResult
    {
        $credentials = $this->credentials;
        $config      = $this->config;

        if ($credentials === []) {
            $provider = $this->provider ?? MobileRechargeProvider::query()
                ->where('code', $recharge->provider)
                ->with('plugin')
                ->first();

            if (! $provider) {
                throw new NotifyErrorException(__('Reloadly provider configuration is missing.'));
            }

            $credentials = $provider->credentials();
            $config      = is_array($provider->config) ? $provider->config : [];
        }

        $clientId     = (string) ($credentials['client_id'] ?? '');
        $clientSecret = (string) ($credentials['client_secret'] ?? '');
        $isSandbox    = (bool) ($credentials['sandbox'] ?? true);
        $operatorId   = $config['default_operator_id'] ?? null;

        if ($clientId === '' || $clientSecret === '') {
            throw new NotifyErrorException(__('Reloadly credentials are not configured.'));
        }

        $token = $this->fetchAccessToken($recharge->provider, $clientId, $clientSecret, $isSandbox);

        try {
            $response = Http::baseUrl($isSandbox ? self::SANDBOX_BASE : self::PRODUCTION_BASE)
                ->withToken($token)
                ->acceptJson()
                ->withHeaders(['Accept' => 'application/com.reloadly.topups-v1+json'])
                ->timeout((int) ($credentials['timeout'] ?? $config['timeout'] ?? 20))
                ->post('/topups', array_filter([
                    'operatorId'       => $operatorId,
                    'amount'           => (float) $recharge->amount,
                    'useLocalAmount'   => (bool) ($config['use_local_amount'] ?? true),
                    'customIdentifier' => 'recharge_'.$recharge->id,
                    'recipientPhone'   => [
                        'countryCode' => strtoupper((string) ($recharge->country ?? ($config['default_country'] ?? 'BD'))),
                        'number'      => $recharge->phone_number,
                    ],
                    'senderPhone' => ! empty($config['sender_phone'])
                        ? [
                            'countryCode' => strtoupper((string) ($config['sender_country'] ?? 'US')),
                            'number'      => (string) $config['sender_phone'],
                        ]
                        : null,
                ], fn ($value) => $value !== null && $value !== ''));
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Reloadly is unreachable: :msg', ['msg' => $e->getMessage()]));
        }

        return $this->mapResponse($response, $recharge);
    }

    private function fetchAccessToken(string $providerCode, string $clientId, string $clientSecret, bool $isSandbox): string
    {
        $cacheKey = self::TOKEN_CACHE_PREFIX.$providerCode.':'.($isSandbox ? 'sandbox' : 'production');

        return (string) Cache::remember($cacheKey, now()->addMinutes(50), function () use ($clientId, $clientSecret, $isSandbox): string {
            $response = Http::acceptJson()
                ->timeout(15)
                ->post(self::AUTH_URL, [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type'    => 'client_credentials',
                    'audience'      => $isSandbox ? self::SANDBOX_AUDIENCE : self::PRODUCTION_AUDIENCE,
                ]);

            if (! $response->successful()) {
                throw new NotifyErrorException(__('Reloadly authentication failed.'));
            }

            $token = (string) data_get($response->json(), 'access_token', '');

            if ($token === '') {
                throw new NotifyErrorException(__('Reloadly returned an empty access token.'));
            }

            return $token;
        });
    }

    private function mapResponse(Response $response, MobileRecharge $recharge): MobileRechargeProviderResult
    {
        $payload = $response->json() ?? [];

        if (! $response->successful()) {
            return new MobileRechargeProviderResult(
                status: MobileRechargeStatus::FAILED,
                reference: 'reloadly_'.$recharge->id,
                message: (string) data_get($payload, 'message', __('Reloadly rejected the request.')),
                payload: $payload,
            );
        }

        $apiStatus = strtoupper((string) data_get($payload, 'status', 'PROCESSING'));
        $status    = match ($apiStatus) {
            'SUCCESSFUL', 'COMPLETED' => MobileRechargeStatus::COMPLETED,
            'FAILED', 'REFUNDED', 'CANCELLED' => MobileRechargeStatus::FAILED,
            default => MobileRechargeStatus::PROCESSING,
        };

        return new MobileRechargeProviderResult(
            status: $status,
            reference: (string) data_get($payload, 'transactionId', 'reloadly_'.$recharge->id),
            message: (string) data_get($payload, 'message', __('Reloadly accepted the recharge.')),
            payload: $payload,
        );
    }
}
