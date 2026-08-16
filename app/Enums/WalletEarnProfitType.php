<?php

namespace App\Enums;

enum WalletEarnProfitType: string
{
    case Fixed      = 'fixed';
    case Percentage = 'percentage';

    public static function options(): array
    {
        return [
            self::Fixed->value      => __('Fixed Amount'),
            self::Percentage->value => __('Percentage'),
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value];
    }
}
