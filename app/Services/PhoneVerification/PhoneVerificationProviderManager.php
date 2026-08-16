<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerification\PhoneVerificationProviderInterface;
use InvalidArgumentException;

class PhoneVerificationProviderManager
{
    public function activeProvider(): PhoneVerificationProviderInterface
    {
        $provider = (string) config('mobile_services.phone_verification.provider', 'log');
        $class    = config("mobile_services.phone_verification.providers.{$provider}");

        if (! is_string($class) || $class === '') {
            throw new InvalidArgumentException("Phone verification provider [{$provider}] is not configured.");
        }

        $instance = app($class);

        if (! $instance instanceof PhoneVerificationProviderInterface) {
            throw new InvalidArgumentException("Phone verification provider [{$provider}] must implement ".PhoneVerificationProviderInterface::class.'.');
        }

        return $instance;
    }
}
