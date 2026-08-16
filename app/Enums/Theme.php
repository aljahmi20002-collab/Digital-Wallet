<?php

namespace App\Enums;

/**
 * Site-wide visual theme. Drives:
 *  - which component schema/view set the page builder shows
 *  - which Blade layout & section partials the frontend renders
 *  - which CSS/JS bundle is loaded.
 *
 * Persisted in the `settings` table under the `active_theme` key; resolved
 * everywhere via {@see activeTheme()} in app/helpers.php.
 */
enum Theme: string
{
    case Classic = 'classic';
    case Golden  = 'golden';

    public function label(): string
    {
        return match ($this) {
            self::Classic => __('Classic'),
            self::Golden  => __('Golden'),
        };
    }

    public function tagline(): string
    {
        return match ($this) {
            self::Classic => __('The original light, Bootstrap-driven look. Friendly, vivid, broad-audience.'),
            self::Golden  => __('Luxury private-banking aesthetic — obsidian + gold, serif display, Swiss-watch poise.'),
        };
    }

    public function previewImage(): string
    {
        return match ($this) {
            self::Classic => 'general/static/theme/classic-preview.svg',
            self::Golden  => 'general/static/theme/golden-preview.svg',
        };
    }

    public function accentColor(): string
    {
        return match ($this) {
            self::Classic => '#4663EE',
            self::Golden  => '#D4AF37',
        };
    }

    public function isDark(): bool
    {
        return $this === self::Golden;
    }

    public static function default(): self
    {
        return self::Classic;
    }

    /**
     * Coerce a raw value (e.g. from settings) into a Theme, falling back to default.
     */
    public static function fromValueOrDefault(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && ($theme = self::tryFrom($value))) {
            return $theme;
        }

        return self::default();
    }
}
