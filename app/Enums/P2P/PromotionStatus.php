<?php

declare(strict_types=1);

namespace App\Enums\P2P;

enum PromotionStatus: string
{
    case ACTIVE    = 'ACTIVE';
    case EXPIRED   = 'EXPIRED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => __('Active'),
            self::EXPIRED   => __('Expired'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE    => 'success',
            self::EXPIRED   => 'dark',
            self::CANCELLED => 'secondary',
        };
    }

    public function badgeClass(): string
    {
        return 'badge bg-'.$this->badgeColor();
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE    => 'star',
            self::EXPIRED   => 'hourglass',
            self::CANCELLED => 'x-circle',
        };
    }
}
