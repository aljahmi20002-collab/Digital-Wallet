<?php

namespace App\Services\MobileRecharge\Drivers;

use App\Contracts\MobileRecharge\MobileRechargeProviderInterface;
use App\Enums\MobileRechargeStatus;
use App\Models\MobileRecharge;
use App\Services\MobileRecharge\MobileRechargeProviderResult;
use Illuminate\Support\Str;

class SandboxMobileRechargeProvider implements MobileRechargeProviderInterface
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
        $statusValue = (string) ($this->credentials['sandbox_status']
            ?? $this->config['sandbox_status']
            ?? setting('mobile_recharge_sandbox_status', config('mobile_services.recharge.sandbox_status')));

        $status = MobileRechargeStatus::tryFrom($statusValue) ?? MobileRechargeStatus::COMPLETED;

        return new MobileRechargeProviderResult(
            status: $status,
            reference: 'sandbox_'.Str::lower(Str::random(16)),
            message: $status === MobileRechargeStatus::FAILED
                ? __('Sandbox provider marked this recharge as failed.')
                : __('Sandbox provider accepted the recharge.'),
            payload: [
                'provider'     => 'sandbox',
                'phone_number' => $recharge->phone_number,
                'amount'       => $recharge->amount,
            ],
        );
    }
}
