<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentLinkStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => __('Active'),
            self::INACTIVE => __('Inactive'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::INACTIVE => 'secondary',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn ($case) => $case->value, self::cases()),
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
