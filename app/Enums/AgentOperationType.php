<?php

namespace App\Enums;

enum AgentOperationType: string
{
    case CASH_IN  = 'cash_in';
    case CASH_OUT = 'cash_out';

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
            self::CASH_IN  => __('Cash In'),
            self::CASH_OUT => __('Cash Out'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CASH_IN  => 'success',
            self::CASH_OUT => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH_IN  => 'fa-arrow-down',
            self::CASH_OUT => 'fa-arrow-up',
        };
    }
}
