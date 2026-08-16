<?php

namespace App\Enums\P2P;

enum DisputeStatus: string
{
    case OPEN     = 'OPEN';
    case RESOLVED = 'RESOLVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN     => __('Open'),
            self::RESOLVED => __('Resolved'),
            self::REJECTED => __('Rejected'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::OPEN     => 'warning',
            self::RESOLVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return 'badge bg-' . $this->badgeColor();
    }

    public function icon(): string
    {
        return match ($this) {
            self::OPEN     => 'alert-circle',
            self::RESOLVED => 'check-circle',
            self::REJECTED => 'x-circle',
        };
    }
}
