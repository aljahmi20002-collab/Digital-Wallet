<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CurrencyService
{
    public const string DEFAULT_CURRENCY_CACHE_KEY = 'default_currency';

    public const string ALL_CURRENCIES_CACHE_KEY = 'all_currencies';

    /**
     * Get the default currency code, cached for efficiency.
     */
    public function getDefaultCurrency(): array
    {
        if (! Schema::hasTable('currencies')) {
            return [];
        }

        return Cache::remember(self::DEFAULT_CURRENCY_CACHE_KEY, now()->addDay(), function () {
            return Currency::query()
                ->where('default', true)
                ->first(['code', 'symbol'])
                ?->toArray() ?? [];
        });
    }

    public function exists($currencyCode): bool
    {
        return Currency::where('code', $currencyCode)->exists();
    }

    /**
     * Get a list of all active currencies, cached.
     */
    public function getAllCurrencies()
    {
        return Cache::remember(self::ALL_CURRENCIES_CACHE_KEY, now()->addDay(), function () {
            return Currency::where('status', true)->get();
        });
    }

    public function getCurrencyByCode($code): ?Currency
    {
        return Cache::remember("currency_by_code_{$code}", now()->addDay(), function () use ($code) {
            return Currency::where('code', $code)->first();
        });
    }

    /**
     * Clear cached currency data.
     */
    public function clearCurrencyCache(): void
    {
        Cache::forget(self::DEFAULT_CURRENCY_CACHE_KEY);
        Cache::forget(self::ALL_CURRENCIES_CACHE_KEY);
    }
}
