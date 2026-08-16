<?php

namespace App\Services\VirtualCard;

use App\Services\VirtualCard\Drivers\VirtualCardProviderInterface;
use Exception;

class VirtualCardProviderFactory
{
    /**
     * Resolve a provider implementation by its code.
     *
     * The mapping lives in config/virtual_card.php so adding a new gateway
     * does not require editing this class.
     */
    public function getProvider(string $providerCode): VirtualCardProviderInterface
    {
        $class = config("virtual_card.providers.$providerCode");

        if (! $class || ! class_exists($class)) {
            throw new Exception("Unsupported virtual card provider: $providerCode");
        }

        $instance = app($class);

        if (! $instance instanceof VirtualCardProviderInterface) {
            throw new Exception("Provider '$providerCode' must implement VirtualCardProviderInterface.");
        }

        return $instance;
    }
}
