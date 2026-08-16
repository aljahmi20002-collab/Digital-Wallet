<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class Plugin extends Model
{
    use HasFactory;

    public const TYPE_MOBILE_RECHARGE = 'mobile_recharge';

    protected $fillable = [
        'code',
        'name',
        'type',
        'logo',
        'description',
        'credentials',
        'fields',
        'status',
    ];

    public static function credentials($code): mixed
    {

        return Cache::rememberForever($code, function () use ($code) {
            $plugin = self::where('code', $code)->first();

            if (! $plugin) {
                return [];
            }

            $credentials = json_decode((string) $plugin->credentials, true);
            if (! is_array($credentials)) {
                $credentials = [];
            }

            $credentials['status'] = $plugin->status;

            return $credentials;
        });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeMobileRecharge(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MOBILE_RECHARGE);
    }

    /**
     * Decoded credentials JSON. Empty array when blank or invalid.
     *
     * @return array<string, mixed>
     */
    public function credentialsArray(): array
    {
        $decoded = json_decode((string) $this->credentials, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function credentialValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->credentialsArray(), $key, $default);
    }

    public function rechargeProvider(): HasOne
    {
        return $this->hasOne(MobileRechargeProvider::class, 'plugin_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $plugin): void {
            self::flushCache($plugin->code);

            if ($plugin->type === self::TYPE_MOBILE_RECHARGE) {
                MobileRechargeProvider::flushProviderCache();
            }
        });

        static::deleted(function (self $plugin): void {
            self::flushCache($plugin->code);

            if ($plugin->type === self::TYPE_MOBILE_RECHARGE) {
                MobileRechargeProvider::flushProviderCache();
            }
        });
    }

    private static function flushCache($code): void
    {
        Cache::forget($code);
        Cache::forget('plugins_data');
    }
}
