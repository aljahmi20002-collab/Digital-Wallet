<?php

namespace App\Services\MobileRecharge;

use App\Enums\MobileRechargeStatus;

class MobileRechargeProviderResult
{
    public function __construct(
        public readonly MobileRechargeStatus $status,
        public readonly string $reference,
        public readonly ?string $message = null,
        public readonly array $payload = [],
    ) {}
}
