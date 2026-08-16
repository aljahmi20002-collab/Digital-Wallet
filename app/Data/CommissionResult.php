<?php

namespace App\Data;

use App\Enums\AgentCommissionRuleType;

class CommissionResult
{
    /**
     * @param array<string, mixed> $snapshot
     */
    public function __construct(
        public readonly ?int $ruleId,
        public readonly string $source,
        public readonly ?AgentCommissionRuleType $calculationType,
        public readonly float $percentageRate,
        public readonly float $fixedAmount,
        public readonly float $amount,
        public readonly array $snapshot = [],
    ) {}

    public static function none(): self
    {
        return new self(
            ruleId: null,
            source: 'none',
            calculationType: null,
            percentageRate: 0,
            fixedAmount: 0,
            amount: 0,
            snapshot: ['source' => 'none']
        );
    }
}
