<?php

namespace App\Enums;

use Carbon\Carbon;

enum BillingCycle: string
{
    case Daily      = 'daily';
    case Weekly     = 'weekly';
    case Monthly    = 'monthly';
    case HalfYearly = 'half_yearly';
    case Yearly     = 'yearly';
    case Lifetime   = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Daily      => __('Daily'),
            self::Weekly     => __('Weekly'),
            self::Monthly    => __('Monthly'),
            self::HalfYearly => __('Half Yearly'),
            self::Yearly     => __('Yearly'),
            self::Lifetime   => __('Lifetime'),
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Daily      => __('/day'),
            self::Weekly     => __('/week'),
            self::Monthly    => __('/mo'),
            self::HalfYearly => __('/6mo'),
            self::Yearly     => __('/yr'),
            self::Lifetime   => __('one-time'),
        };
    }

    public function isLifetime(): bool
    {
        return $this === self::Lifetime;
    }

    /**
     * Returns the number of months for this billing cycle.
     * Returns null for lifetime (never expires).
     */
    public function toMonths(): ?int
    {
        return match ($this) {
            self::Daily      => null,
            self::Weekly     => null,
            self::Monthly    => 1,
            self::HalfYearly => 6,
            self::Yearly     => 12,
            self::Lifetime   => null,
        };
    }

    /**
     * Returns the number of days for this billing cycle.
     * Returns null for lifetime (never expires).
     */
    public function toDays(): ?int
    {
        return match ($this) {
            self::Daily      => 1,
            self::Weekly     => 7,
            self::Monthly    => 30,
            self::HalfYearly => 180,
            self::Yearly     => 365,
            self::Lifetime   => null,
        };
    }

    /**
     * Calculate end date from a given start date.
     */
    public function calculateEndDate(Carbon $start): ?Carbon
    {
        return match ($this) {
            self::Daily      => $start->copy()->addDay(),
            self::Weekly     => $start->copy()->addWeek(),
            self::Monthly    => $start->copy()->addMonth(),
            self::HalfYearly => $start->copy()->addMonths(6),
            self::Yearly     => $start->copy()->addYear(),
            self::Lifetime   => null,
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn ($c) => $c->value, self::cases()),
            array_map(fn ($c) => $c->label(), self::cases())
        );
    }
}
