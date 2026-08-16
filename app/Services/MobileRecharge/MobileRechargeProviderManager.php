<?php

namespace App\Services\MobileRecharge;

use App\Contracts\MobileRecharge\MobileRechargeProviderInterface;
use App\Models\MobileRechargeProvider;
use App\Services\MobileRecharge\Drivers\HttpMobileRechargeProvider;
use App\Services\MobileRecharge\Drivers\ReloadlyMobileRechargeProvider;
use App\Services\MobileRecharge\Drivers\SandboxMobileRechargeProvider;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

/**
 * Resolves the active mobile recharge provider.
 *
 * Provider business rules (fee, limits, default flag, supported regions)
 * live in `mobile_recharge_providers`. The credentials for the underlying
 * driver live in the matching `plugins` row referenced via
 * MobileRechargeProvider->plugin.
 */
class MobileRechargeProviderManager
{
    public function activeProvider(): MobileRechargeProviderInterface
    {
        $provider = $this->resolveActiveProvider();

        if ($provider) {
            return $this->buildDriver($provider);
        }

        return $this->buildLegacyDriver($this->providerCode());
    }

    public function providerCode(): string
    {
        $provider = $this->resolveActiveProvider();

        if ($provider) {
            return (string) $provider->code;
        }

        return (string) setting('mobile_recharge_provider', config('mobile_services.recharge.provider', 'sandbox'));
    }

    public function resolveActiveProvider(): ?MobileRechargeProvider
    {
        if (! $this->providersTableAvailable()) {
            return null;
        }

        $code = (string) setting('mobile_recharge_provider', '');

        if ($code !== '') {
            $byCode = MobileRechargeProvider::query()
                ->active()
                ->where('code', $code)
                ->with('plugin')
                ->first();

            if ($byCode) {
                return $byCode;
            }
        }

        return MobileRechargeProvider::default();
    }

    public function buildDriver(MobileRechargeProvider $provider): MobileRechargeProviderInterface
    {
        $instance    = $this->buildLegacyDriver((string) $provider->driver);
        $credentials = $provider->credentials();
        $config      = is_array($provider->config) ? $provider->config : [];

        $this->hydrate($instance, $provider, $credentials, $config);

        return $instance;
    }

    private function buildLegacyDriver(string $code): MobileRechargeProviderInterface
    {
        $class = config("mobile_services.recharge.providers.{$code}");

        if (! is_string($class) || $class === '') {
            throw new InvalidArgumentException("Mobile recharge driver [{$code}] is not registered.");
        }

        $instance = app($class);

        if (! $instance instanceof MobileRechargeProviderInterface) {
            throw new InvalidArgumentException("Mobile recharge driver [{$code}] must implement ".MobileRechargeProviderInterface::class.'.');
        }

        return $instance;
    }

    /**
     * @param array<string, mixed> $credentials
     * @param array<string, mixed> $config
     */
    private function hydrate(
        MobileRechargeProviderInterface $instance,
        MobileRechargeProvider $provider,
        array $credentials,
        array $config,
    ): void {
        if ($instance instanceof ReloadlyMobileRechargeProvider) {
            $instance->configure($provider, $credentials, $config);

            return;
        }

        if ($instance instanceof HttpMobileRechargeProvider) {
            $instance->configureFromCredentials($credentials, $config);

            return;
        }

        if ($instance instanceof SandboxMobileRechargeProvider) {
            $instance->configureFromCredentials($credentials, $config);
        }
    }

    private function providersTableAvailable(): bool
    {
        try {
            return Schema::hasTable('mobile_recharge_providers');
        } catch (Throwable) {
            return false;
        }
    }
}
