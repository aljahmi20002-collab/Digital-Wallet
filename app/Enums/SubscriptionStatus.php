<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Trial     = 'trial';
    case Grace     = 'grace';
    case Expired   = 'expired';
    case Cancelled = 'cancelled';
    case Failed    = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => __('Pending'),
            self::Active    => __('Active'),
            self::Trial     => __('Trial'),
            self::Grace     => __('Grace Period'),
            self::Expired   => __('Expired'),
            self::Cancelled => __('Cancelled'),
            self::Failed    => __('Failed'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active    => 'success',
            self::Trial     => 'info',
            self::Grace     => 'warning',
            self::Pending   => 'secondary',
            self::Expired   => 'danger',
            self::Cancelled => 'dark',
            self::Failed    => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active    => 'check',
            self::Trial     => 'notification',
            self::Grace     => 'warning',
            self::Pending   => 'schedule',
            self::Expired   => 'warning-2',
            self::Cancelled => 'close',
            self::Failed    => 'warning-2',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Trial, self::Grace]);
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn ($c) => $c->value, self::cases()),
            array_map(fn ($c) => $c->label(), self::cases())
        );
    }
}
