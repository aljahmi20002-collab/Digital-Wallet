<?php

namespace App\Models;

use App\Models\P2P\P2PSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $guarded = [];

    private const BRAND_ASSET_DEFAULT = 'general/static/logo/digikash-mark.svg';

    private const BRAND_ASSET_KEYS = [
        'logo',
        'light_logo',
        'small_logo',
        'site_favicon',
    ];

    private const LEGACY_BRAND_ASSET_DEFAULTS = [
        'img/logo.png',
        'img/favicon.png',
    ];

    private static function isP2PKey(string $key): bool
    {
        return str_starts_with($key, 'p2p_');
    }

    private static function castValueForWrite(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => (bool) $value,
            default => $value,
        };
    }

    /**
     * Get all settings, using cache for performance.
     */
    public static function getAllSettings(): Collection
    {
        return Cache::rememberForever('settings.all', function () {
            return self::all();
        });
    }

    /**
     * Check if a given key exists in settings.
     */
    public static function has(string $key): bool
    {
        return self::getAllSettings()->contains('key', $key);
    }

    /**
     * Get validation rules for all fields of a section.
     */
    public static function getValidationRules(string $section): array
    {
        return self::getDefinedFields($section)
            ->filter(fn ($field) => ! empty($field['rules']))
            ->pluck('rules', 'key')
            ->toArray();
    }

    /**
     * Get readable field labels for validation errors.
     *
     * @return array<string, string>
     */
    public static function getValidationAttributes(string $section): array
    {
        return self::getDefinedFields($section)
            ->filter(fn ($field) => ! empty($field['key']))
            ->mapWithKeys(fn ($field): array => [
                $field['key'] => (string) ($field['label'] ?? str_replace('_', ' ', $field['key'])),
            ])
            ->toArray();
    }

    /**
     * Get settings-specific validation messages.
     *
     * @return array<string, string>
     */
    public static function getValidationMessages(string $section): array
    {
        return self::getDefinedFields($section)
            ->filter(fn ($field): bool => ($field['type'] ?? null) === 'img' && ! empty($field['key']))
            ->flatMap(fn ($field): array => [
                $field['key'].'.mimes'    => __(':attribute must be one of these file types: :values.'),
                $field['key'].'.max'      => __(':attribute must not be larger than :max KB.'),
                $field['key'].'.uploaded' => __(':attribute could not be uploaded. Check the file size and try again.'),
            ])
            ->toArray();
    }

    /**
     * Get the data type for a given field in a section.
     */
    public static function getDataType(string $field, string $section): string
    {
        return self::getDefinedFields($section)->pluck('data', 'key')->get($field, 'string');
    }

    /**
     * Set a value for a key, creating or updating as needed.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): mixed
    {
        if (self::isP2PKey($key) && Schema::hasTable('p2p_settings')) {
            $attribute = P2PSetting::KEY_MAP[$key] ?? null;
            if ($attribute) {
                $record = P2PSetting::query()->first();
                if (! $record) {
                    $record = P2PSetting::query()->create([]);
                }

                $record->update([
                    $attribute => self::castValueForWrite($value, $type),
                ]);

                return $value;
            }
        }

        $setting = self::query()->where('key', $key)->first();

        if ($setting) {
            $setting->update(['val' => $value, 'type' => $type]);
            self::flushCache();

            return $value;
        }

        return self::add($key, $value, $type);
    }

    /**
     * Add a new setting if it does not exist.
     */
    public static function add(string $key, mixed $value, string $type = 'string'): mixed
    {
        if (self::isP2PKey($key) && Schema::hasTable('p2p_settings')) {
            return self::set($key, $value, $type);
        }

        $created = self::query()->updateOrCreate(
            ['key' => $key],
            ['val' => $value, 'type' => $type],
        );
        self::flushCache();

        return $created ? $value : false;
    }

    /**
     * Remove a setting by key.
     */
    public static function remove(string $key): bool
    {
        $deleted = self::where('key', $key)->delete();
        if ($deleted) {
            self::flushCache();

            return true;
        }

        return false;
    }

    /**
     * Get the default value for a specific field in a section.
     */
    public static function getDefaultValueForField(string $field, string $section): mixed
    {
        return self::getDefinedFields($section)->pluck('value', 'key')->get($field);
    }

    /**
     * Get a setting value. Falls back to config default if not set.
     */
    public static function get(string $key, ?string $section = null, mixed $default = null): mixed
    {
        if (self::isP2PKey($key) && Schema::hasTable('p2p_settings')) {
            $attribute = P2PSetting::KEY_MAP[$key] ?? null;
            if ($attribute) {
                $record = P2PSetting::current();
                if ($record) {
                    return $record->getAttribute($attribute);
                }
            }

            return self::getDefaultValue($key, $section, $default);
        }

        $setting = self::getAllSettings()->firstWhere('key', $key);
        if ($setting) {
            return self::normalizeBrandAssetValue($key, self::castValue($setting->val, $setting->type));
        }

        return self::getDefaultValue($key, $section, $default);
    }

    /**
     * Flush the cached settings.
     */
    public static function flushCache(): void
    {
        Cache::forget('settings.all');
    }

    /**
     * Eloquent model boot: flush cache on create, update, or delete.
     */
    protected static function booted(): void
    {
        static::created(fn () => self::flushCache());
        static::updated(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    /**
     * Get defined fields for a section from config/settings.php.
     */
    private static function getDefinedFields(string $section): Collection
    {
        return collect(config('settings')[$section]['elements'] ?? []);
    }

    /**
     * Cast the value to its intended type.
     */
    private static function castValue(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => (bool) $value,
            default => $value,
        };
    }

    /**
     * Get the default value for a key/section, falling back to $default.
     */
    private static function getDefaultValue(string $key, ?string $section, mixed $default): mixed
    {
        if ($default !== null) {
            return self::normalizeBrandAssetValue($key, $default);
        }

        if ($section) {
            return self::normalizeBrandAssetValue($key, self::getDefaultValueForField($key, $section));
        }

        foreach (config('settings') as $settingsSection) {
            if (! is_array($settingsSection)) {
                continue;
            }

            $value = collect($settingsSection['elements'] ?? [])->firstWhere('key', $key)['value'] ?? null;

            if ($value !== null) {
                return self::normalizeBrandAssetValue($key, $value);
            }
        }

        return null;
    }

    private static function normalizeBrandAssetValue(string $key, mixed $value): mixed
    {
        if (! in_array($key, self::BRAND_ASSET_KEYS, true)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '' || in_array($value, self::LEGACY_BRAND_ASSET_DEFAULTS, true)) {
            return self::BRAND_ASSET_DEFAULT;
        }

        $value = self::normalizeStoredAssetPath($value);

        if ($key !== 'site_favicon' || self::brandAssetExists($value)) {
            return $value;
        }

        return self::BRAND_ASSET_DEFAULT;
    }

    private static function normalizeStoredAssetPath(string $value): string
    {
        $path = ltrim($value, '/');

        if (filter_var($value, FILTER_VALIDATE_URL) || str_starts_with($path, 'storage/')) {
            return $value;
        }

        if (is_file(public_path($path))) {
            return $path;
        }

        return Storage::disk('public')->exists($path) ? 'storage/'.$path : $value;
    }

    private static function brandAssetExists(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }

        $path        = ltrim($value, '/');
        $storagePath = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        return is_file(public_path($path)) || Storage::disk('public')->exists($storagePath);
    }
}
