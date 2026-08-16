<?php

namespace App\Enums;

enum MobileRechargeStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::COMPLETED  => __('Completed'),
            self::FAILED     => __('Failed'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING    => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED  => 'success',
            self::FAILED     => 'danger',
        };
    }
}
