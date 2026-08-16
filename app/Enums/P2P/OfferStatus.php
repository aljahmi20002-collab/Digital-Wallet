<?php

namespace App\Enums\P2P;

enum OfferStatus: string
{
    case ACTIVE   = 'ACTIVE';
    case PAUSED   = 'PAUSED';
    case DISABLED = 'DISABLED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => __('Active'),
            self::PAUSED   => __('Paused'),
            self::DISABLED => __('Disabled'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::PAUSED   => 'warning',
            self::DISABLED => 'secondary',
        };
    }

    public function badgeClass(): string
    {
        return 'badge bg-' . $this->badgeColor();
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE   => 'play-circle',
            self::PAUSED   => 'pause-circle',
            self::DISABLED => 'ban',
        };
    }
}
