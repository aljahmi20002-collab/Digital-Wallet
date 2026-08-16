<?php

declare(strict_types=1);

namespace App\Models\P2P;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class P2PSetting extends Model
{
    protected $table = 'p2p_settings';

    protected $guarded = [];

    protected $casts = [
        'enabled'                => 'boolean',
        'maker_fee_pct'          => 'decimal:4',
        'taker_fee_pct'          => 'decimal:4',
        'order_expiry_minutes'   => 'integer',
        'dispute_window_minutes' => 'integer',
        'min_amount'             => 'decimal:8',
        'max_amount'             => 'decimal:8',
    ];

    /**
     * Legacy `settings` table keys → `p2p_settings` model columns. Used by
     * App\Models\Setting::get/set to transparently bridge legacy reads and
     * writes for these P2P keys. Do not call Setting::set for these keys
     * from inside this model — it will recurse into the bridge.
     */
    public const KEY_MAP = [
        'p2p_enabled'                => 'enabled',
        'p2p_maker_fee_pct'          => 'maker_fee_pct',
        'p2p_taker_fee_pct'          => 'taker_fee_pct',
        'p2p_order_expiry_minutes'   => 'order_expiry_minutes',
        'p2p_dispute_window_minutes' => 'dispute_window_minutes',
        'p2p_min_amount'             => 'min_amount',
        'p2p_max_amount'             => 'max_amount',
        'p2p_allowed_countries'      => 'allowed_countries',
        'p2p_blocked_countries'      => 'blocked_countries',
    ];

    public static function current(): ?self
    {
        if (! Schema::hasTable('p2p_settings')) {
            return null;
        }

        return Cache::rememberForever('p2p_settings.current', function () {
            return self::query()->first();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('p2p_settings.current');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
