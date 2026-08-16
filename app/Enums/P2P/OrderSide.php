<?php

namespace App\Enums\P2P;

enum OrderSide: string
{
    case BUY  = 'BUY';
    case SELL = 'SELL';

    public function label(): string
    {
        return match ($this) {
            self::BUY  => __('Buy'),
            self::SELL => __('Sell'),
        };
    }

    public function badgeColor(): string
    {
        // Follow marketplace convention: Buy=success (green), Sell=danger (red)
        return match ($this) {
            self::BUY  => 'success',
            self::SELL => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return 'badge bg-' . $this->badgeColor();
    }

    public function icon(): string
    {
        // Icon tokens for <x-icon name="..."/> or mapping layer
        return match ($this) {
            self::BUY  => 'arrow-down-circle',
            self::SELL => 'arrow-up-circle',
        };
    }
}
