<?php

namespace App\Services\MobileRecharge\Drivers;

use App\Contracts\MobileRecharge\MobileRechargeProviderInterface;
use App\Enums\MobileRechargeStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\MobileRecharge;
use App\Services\MobileRecharge\MobileRechargeProviderResult;
use Illuminate\Support\Facades\Http;

class HttpMobileRechargeProvider implements MobileRechargeProviderInterface
{
    /** @var array<string, mixed> */
    private array $credentials = [];

    /** @var array<string, mixed> */
    private array $config = [];

    /**
     * @param array<string, mixed> $credentials
     * @param array<string, mixed> $config
     */
    public function configureFromCredentials(array $credentials, array $config = []): void
    {
        $this->credentials = $credentials;
        $this->config      = $config;
    }

    public function recharge(MobileRecharge $recharge): MobileRechargeProviderResult
    {
        $baseUrl = (string) ($this->credentials['base_url']
            ?? setting('mobile_recharge_http_base_url', config('mobile_services.recharge.http.base_url')));
        $token = (string) ($this->credentials['token']
            ?? setting('mobile_recharge_http_token', config('mobile_services.recharge.http.token')));

        if ($baseUrl === '' || $token === '') {
            throw new NotifyErrorException(__('Mobile recharge provider credentials are not configured.'));
        }

        $endpoint = (string) ($this->credentials['endpoint']
            ?? setting('mobile_recharge_http_endpoint', config('mobile_services.recharge.http.endpoint', '/recharges')));
        $timeout = (int) ($this->credentials['timeout']
            ?? setting('mobile_recharge_http_timeout', config('mobile_services.recharge.http.timeout', 15)));

        $response = Http::baseUrl($baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, [
                'reference'    => $recharge->id,
                'phone_number' => $recharge->phone_number,
                'operator'     => $recharge->operator,
                'country'      => $recharge->country,
                'amount'       => $recharge->amount,
                'currency'     => $recharge->currency,
                'metadata'     => [
                    'user_id'        => $recharge->user_id,
                    'wallet_id'      => $recharge->wallet_id,
                    'transaction_id' => $recharge->transaction?->trx_id,
                ],
            ]);

        if (! $response->successful()) {
            throw new NotifyErrorException(__('Mobile recharge provider rejected the request.'));
        }

        $payload = $response->json() ?? [];
        $status  = MobileRechargeStatus::tryFrom((string) data_get($payload, 'status', 'processing'))
            ?? MobileRechargeStatus::PROCESSING;

        return new MobileRechargeProviderResult(
            status: $status,
            reference: (string) data_get($payload, 'reference', data_get($payload, 'data.reference', 'http_'.$recharge->id)),
            message: (string) data_get($payload, 'message', __('Mobile recharge provider accepted the request.')),
            payload: $payload,
        );
    }
}
