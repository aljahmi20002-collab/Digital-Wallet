<?php

namespace App\Models;

use Database\Factories\MobileRechargeProviderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Business rules for a mobile recharge provider.
 *
 * Mirrors the `virtual_card_providers` pattern: business config (fees,
 * limits, regions, default flag) lives here while the per-driver API
 * credentials live in the matching `plugins` row referenced by
 * `plugin_id`.
 */
class MobileRechargeProvider extends Model
{
    /** @use HasFactory<MobileRechargeProviderFactory> */
    use HasFactory;

    public const CACHE_KEY_DEFAULT = 'mobile_recharge_default_provider';

    protected $fillable = [
        'plugin_id',
        'code',
        'name',
        'driver',
        'logo',
        'description',
        'status',
        'is_default',
        'supported_countries',
        'supported_currencies',
        'fee_fixed',
        'fee_percent',
        'min_amount',
        'max_amount',
        'config',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'status'               => 'boolean',
            'is_default'           => 'boolean',
            'supported_countries'  => 'array',
            'supported_currencies' => 'array',
            'config'               => 'array',
            'fee_fixed'            => 'float',
            'fee_percent'          => 'float',
            'min_amount'           => 'float',
            'max_amount'           => 'float',
        ];
    }

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }

    public function recharges(): HasMany
    {
        return $this->hasMany(MobileRecharge::class, 'provider', 'code');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        $plugin = $this->plugin;

        if (! $plugin) {
            return [];
        }

        return $plugin->credentialsArray();
    }

    public function calculateFee(float $amount): float
    {
        $fixed   = (float) ($this->fee_fixed ?? 0);
        $percent = (float) ($this->fee_percent ?? 0);

        return round($fixed + ($amount * $percent / 100), 8);
    }

    public function supportsCurrency(?string $currency): bool
    {
        if (! $currency) {
            return true;
        }

        $list = $this->supported_currencies;

        if (! is_array($list) || $list === []) {
            return true;
        }

        return in_array(strtoupper($currency), array_map('strtoupper', $list), true);
    }

    public function supportsCountry(?string $country): bool
    {
        if (! $country) {
            return true;
        }

        $list = $this->supported_countries;

        if (! is_array($list) || $list === []) {
            return true;
        }

        return in_array(strtoupper($country), array_map('strtoupper', $list), true);
    }

    public function isAmountWithinLimits(float $amount): bool
    {
        $min = (float) ($this->min_amount ?? 0);
        $max = $this->max_amount !== null ? (float) $this->max_amount : null;

        if ($min > 0 && $amount < $min) {
            return false;
        }

        if ($max !== null && $max > 0 && $amount > $max) {
            return false;
        }

        return true;
    }

    public function logoUrl(): string
    {
        return $this->logo
            ? asset($this->logo)
            : asset('general/static/plugins/mobile-recharge.svg');
    }

    public static function flushProviderCache(): void
    {
        Cache::forget(self::CACHE_KEY_DEFAULT);
    }

    public static function default(): ?self
    {
        return Cache::remember(self::CACHE_KEY_DEFAULT, now()->addHour(), function (): ?self {
            return self::query()
                ->active()
                ->where('is_default', true)
                ->orderBy('order')
                ->with('plugin')
                ->first()
                ?? self::query()->active()->orderBy('order')->with('plugin')->first();
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushProviderCache());
        static::deleted(fn () => self::flushProviderCache());
    }
}
