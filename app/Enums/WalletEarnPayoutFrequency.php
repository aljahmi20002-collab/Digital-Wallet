<?php

namespace App\Enums;

enum WalletEarnPayoutFrequency: string
{
    case Daily     = 'daily';
    case Weekly    = 'weekly';
    case Monthly   = 'monthly';
    case EndOfTerm = 'end_of_term';

    public static function options(): array
    {
        return [
            self::Daily->value     => __('Daily'),
            self::Weekly->value    => __('Weekly'),
            self::Monthly->value   => __('Monthly'),
            self::EndOfTerm->value => __('End of Term'),
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value];
    }

    public function intervalLabel(): string
    {
        return match ($this) {
            self::Daily     => __('Every day'),
            self::Weekly    => __('Every week'),
            self::Monthly   => __('Every month'),
            self::EndOfTerm => __('At maturity'),
        };
    }
}
