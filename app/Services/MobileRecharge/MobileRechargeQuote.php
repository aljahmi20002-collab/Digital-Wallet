<?php

namespace App\Services\MobileRecharge;

class MobileRechargeQuote
{
    public function __construct(
        public readonly float $amount,
        public readonly float $fee,
        public readonly float $total,
        public readonly string $currency,
    ) {}
}
