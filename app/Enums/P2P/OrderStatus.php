<?php

declare(strict_types=1);

namespace App\Enums\P2P;

enum OrderStatus: string
{
    case PENDING   = 'PENDING';
    case PAID      = 'PAID';
    case CANCELLED = 'CANCELLED';
    case EXPIRED   = 'EXPIRED';
    case COMPLETED = 'COMPLETED';
    case DISPUTED  = 'DISPUTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => __('Pending'),
            self::PAID      => __('Buyer marked as Paid'),
            self::CANCELLED => __('Cancelled'),
            self::EXPIRED   => __('Expired'),
            self::COMPLETED => __('Completed'),
            self::DISPUTED  => __('In Dispute'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING   => 'secondary',
            self::PAID      => 'warning',
            self::CANCELLED => 'danger',
            self::EXPIRED   => 'dark',
            self::COMPLETED => 'success',
            self::DISPUTED  => 'info',
        };
    }

    public function badgeClass(): string
    {
        return 'badge bg-' . $this->badgeColor();
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING   => 'clock',
            self::PAID      => 'money',
            self::CANCELLED => 'x-circle',
            self::EXPIRED   => 'hourglass',
            self::COMPLETED => 'check-circle',
            self::DISPUTED  => 'alert-circle',
        };
    }
}
