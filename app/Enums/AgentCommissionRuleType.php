<?php

namespace App\Enums;

enum AgentCommissionRuleType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED      = 'fixed';

    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => __('Percentage'),
            self::FIXED      => __('Fixed'),
        };
    }
}
