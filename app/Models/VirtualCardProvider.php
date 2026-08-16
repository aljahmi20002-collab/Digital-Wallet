<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class VirtualCardProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'brand',
        'brand_color',
        'display_label',
        'description',
        'supported_networks',
        'supported_currencies',
        'supported_countries',
        'issue_fee',
        'issue_fee_pct',
        'min_balance',
        'status',
        'config',
        'capabilities',
        'payment_gateway_id',
        'order',
    ];

    protected $casts = [
        'supported_networks'   => 'array',
        'supported_currencies' => 'array',
        'supported_countries'  => 'array',
        'issue_fee'            => 'float',
        'issue_fee_pct'        => 'float',
        'min_balance'          => 'float',
        'status'               => 'boolean',
        'config'               => 'array',
        'capabilities'         => 'array',
    ];

    // Relationships
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeForNetwork(Builder $query, string $network): Builder
    {
        return $query->whereJsonContains('supported_networks', $network);
    }

    public function scopeForCurrency(Builder $query, string $currency): Builder
    {
        return $query->whereJsonContains('supported_currencies', $currency);
    }

    /**
     * Scope: providers that support issuance for the given ISO-2 country
     * code. Providers with NULL `supported_countries` are treated as
     * unrestricted ("issue anywhere") so legacy rows keep working.
     */
    public function scopeForCountry(Builder $query, ?string $country): Builder
    {
        if (! $country) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($country): void {
            $q->whereNull('supported_countries')
                ->orWhereJsonContains('supported_countries', strtoupper($country));
        });
    }

    // Accessors
    public function getFeeFormattedAttribute(): string
    {
        return siteCurrency('symbol').number_format($this->issue_fee, 2);
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->logo ? asset($this->logo) : asset('general/static/default/payment-gateway.png');
    }

    public function getNetworksListAttribute(): string
    {
        return is_array($this->supported_networks) ? implode(', ', array_map('ucfirst', $this->supported_networks)) : '';
    }

    public function getCurrenciesListAttribute(): string
    {
        return is_array($this->supported_currencies) ? implode(', ', $this->supported_currencies) : '';
    }

    // Calculations
    public function issueTotalFee(float $baseAmount = 0.0): float
    {
        $fixed   = (float) ($this->issue_fee ?? 0);
        $percent = (float) ($this->issue_fee_pct ?? 0);

        $base  = max(0, $baseAmount);
        $extra = ($base > 0 && $percent > 0)
            ? round(($base * $percent) / 100, 2)
            : 0.0;

        return round(max(0, $fixed) + $extra, 2);
    }

    /**
     * Resolve the merged capability map for this provider.
     *
     * Order: DB column → config/virtual_card.php override → defaults.
     * Adding a new capability key only needs an entry in default_capabilities.
     */
    public function getResolvedCapabilitiesAttribute(): array
    {
        $defaults = (array) config('virtual_card.default_capabilities', []);
        $override = (array) (config("virtual_card.capabilities.{$this->code}") ?? []);
        $stored   = is_array($this->capabilities) ? $this->capabilities : [];

        return array_merge($defaults, $override, $stored);
    }

    public function supports(string $capability): bool
    {
        return (bool) ($this->resolved_capabilities[$capability] ?? false);
    }

    public function supportsNetwork(?string $network): bool
    {
        if (! $network) {
            return true;
        }

        $list = $this->supported_networks;
        if (! is_array($list) || empty($list)) {
            return true;
        }

        return in_array(strtolower($network), array_map('strtolower', $list), true);
    }

    public function supportsCurrency(?string $currency): bool
    {
        if (! $currency) {
            return true;
        }

        $list = $this->supported_currencies;
        if (! is_array($list) || empty($list)) {
            return true;
        }

        return in_array(strtoupper($currency), array_map('strtoupper', $list), true);
    }

    /**
     * Whether this provider can issue a card for a cardholder whose
     * billing address country is the given ISO-2 code. Empty / NULL
     * `supported_countries` means "no restriction".
     */
    public function supportsCountry(?string $country): bool
    {
        if (! $country) {
            return true;
        }

        $list = $this->supported_countries;
        if (! is_array($list) || empty($list)) {
            return true;
        }

        return in_array(strtoupper($country), array_map('strtoupper', $list), true);
    }

    /**
     * Short, uppercase chip text shown on each mini card / transaction row in
     * the user-facing virtual card dashboard. Falls back to the first 4
     * characters of `code` so the UI never renders an empty pill.
     */
    public function getDashboardLabelAttribute(): string
    {
        $label = trim((string) ($this->display_label ?? ''));

        if ($label !== '') {
            return strtoupper($label);
        }

        return strtoupper(substr((string) $this->code, 0, 4));
    }

    /**
     * Normalised hex (with leading "#") used by the card visual when the admin
     * has pinned a brand color for this provider. Returns null when not set so
     * the front-end can fall through to its rotating theme wheel.
     */
    public function getBrandColorHexAttribute(): ?string
    {
        $value = trim((string) ($this->brand_color ?? ''));

        if ($value === '') {
            return null;
        }

        if ($value[0] !== '#') {
            $value = '#'.$value;
        }

        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value) ? $value : null;
    }

    // Caching (optional)
    public static function allCached()
    {
        return Cache::rememberForever('virtual_card_providers_all', function () {
            return self::active()->orderBy('order')->orderBy('id')->get();
        });
    }

    public static function flushCache(self $provider): void
    {
        Cache::forget('virtual_card_providers_all');
        Cache::forget("virtual_card_provider_code_{$provider->code}");
    }

    protected static function booted()
    {
        static::saved(fn (self $provider) => self::flushCache($provider));
        static::deleted(fn (self $provider) => self::flushCache($provider));
    }
}
