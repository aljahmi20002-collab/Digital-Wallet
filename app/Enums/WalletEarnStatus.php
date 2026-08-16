<?php

namespace App\Enums;

enum WalletEarnStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Completed = 'completed';
    case Canceled  = 'canceled';
    case Rejected  = 'rejected';

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case): string => $case->value, self::cases()),
            array_map(fn (self $case): string => $case->label(), self::cases())
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending   => __('Pending'),
            self::Active    => __('Active'),
            self::Completed => __('Completed'),
            self::Canceled  => __('Canceled'),
            self::Rejected  => __('Rejected'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Active    => 'success',
            self::Completed => 'primary',
            self::Canceled, self::Rejected => 'danger',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Canceled, self::Rejected], true);
    }
}
