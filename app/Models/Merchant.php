<?php

namespace App\Models;

use App\Enums\EnvironmentMode;
use App\Enums\MerchantStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'site_url',
        'currency_id',
        'business_logo',
        'business_description',
        'business_email',
        'fee',
        'status',
        'merchant_key',
        'api_key',
        'api_secret',
        'test_api_key',
        'test_api_secret',
        'test_merchant_key',
        'current_mode',
        'sandbox_enabled',
        'webhook_url',
    ];

    protected $hidden = ['api_key', 'api_secret', 'test_api_key', 'test_api_secret'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'fee'             => 'double',
        'status'          => MerchantStatus::class,
        'sandbox_enabled' => 'boolean',
        'current_mode'    => EnvironmentMode::class,
    ];

    /**
     * Determine if the merchant is approved and active.
     */
    public function isApproved(): bool
    {
        return $this->status === MerchantStatus::APPROVED;
    }

    /**
     * Actions are locked when merchant is disabled or rejected.
     */
    public function isActionLocked(): bool
    {
        return in_array($this->status, [MerchantStatus::DISABLED, MerchantStatus::REJECTED], true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MerchantStatus::APPROVED);
    }

    /**
     * Check if merchant is in sandbox/test mode
     */
    public function isSandboxMode(): bool
    {
        return $this->current_mode === EnvironmentMode::SANDBOX;
    }

    /**
     * Check if merchant is in production mode
     */
    public function isProductionMode(): bool
    {
        return $this->current_mode === EnvironmentMode::PRODUCTION;
    }

    /**
     * Get current API credentials based on active mode
     */
    public function getCurrentApiKey(): ?string
    {
        return $this->isSandboxMode() ? $this->test_api_key : $this->api_key;
    }

    /**
     * Get current API secret based on active mode
     */
    public function getCurrentApiSecret(): ?string
    {
        return $this->isSandboxMode() ? $this->test_api_secret : $this->api_secret;
    }

    /**
     * Get current merchant key based on active mode
     */
    public function getCurrentMerchantKey(): ?string
    {
        return $this->isSandboxMode() ? $this->test_merchant_key : $this->merchant_key;
    }

    /**
     * Switch to sandbox mode
     */
    public function switchToSandbox(): bool
    {
        if (! $this->sandbox_enabled) {
            return false;
        }

        $this->current_mode = EnvironmentMode::SANDBOX;

        return $this->save();
    }

    /**
     * Switch to production mode
     */
    public function switchToProduction(): bool
    {
        $this->current_mode = EnvironmentMode::PRODUCTION;

        return $this->save();
    }

    /**
     * Check if test credentials are set
     */
    public function hasTestCredentials(): bool
    {
        return ! empty($this->test_api_key) && ! empty($this->test_api_secret) && ! empty($this->test_merchant_key);
    }

    /**
     * Generate test credentials if not exists
     */
    public function generateTestCredentials(): void
    {
        if (! $this->hasTestCredentials()) {
            $this->test_api_key      = 'test_'.Str::random(40);
            $this->test_api_secret   = 'test_secret_'.Str::random(32);
            $this->test_merchant_key = 'test_merchant_'.Str::random(16);
            $this->save();
        }
    }

    // App\Models\Merchant.php

    public function scopeFilter($query, Request $request)
    {
        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('status', $request->type);
        });

        $query->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('daterange'), function ($q) use ($request) {
            $dateRange = explode(',', $request->daterange);

            if (count($dateRange) === 2) {
                [$startDate, $endDate] = $dateRange;

                $q->whereBetween('created_at', [
                    Carbon::parse(trim($startDate))->startOfDay(),
                    Carbon::parse(trim($endDate))->endOfDay(),
                ]);
            }
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('merchant_key', 'like', "%{$search}%")
                    ->orWhere('business_email', 'like', "%{$search}%")
                    ->orWhere('site_url', 'like', "%{$search}%");
            });
        });

        return $query;
    }

    public function getBusinessLogoAttribute(?string $value): string
    {
        return $value ?? '/general/static/default/shop.png';
    }

    /**
     * Get the API Key only when the merchant is APPROVED.
     */
    public function getApiKeyAttribute(?string $value): string
    {
        return $this->status === MerchantStatus::APPROVED
            ? ($value ?? __('No API Key Generated'))
            : __('Your API Key will be available after approval.');
    }

    /**
     * Get the API Secret only when the merchant is APPROVED.
     */
    public function getApiSecretAttribute(?string $value): string
    {
        return $this->status === MerchantStatus::APPROVED
            ? ($value ?? __('No API Secret Generated'))
            : __('Your API Secret will be available after approval.');
    }

    /**
     * Get the user that owns the merchant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the currency associated with the merchant.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function supportedCurrencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'merchant_currencies')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(DepositMethod::class, 'merchant_deposit_methods')
            ->withTimestamps();
    }

    /**
     * @return array<int, int>
     */
    public function supportedCurrencyIds(): array
    {
        $ids = $this->relationLoaded('supportedCurrencies')
            ? $this->supportedCurrencies->pluck('id')
            : $this->supportedCurrencies()->pluck('currencies.id');

        if ($ids->isEmpty() && $this->currency_id !== null) {
            $ids = collect([(int) $this->currency_id]);
        }

        return $ids
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function supportsCurrency(int $currencyId): bool
    {
        return in_array($currencyId, $this->supportedCurrencyIds(), true);
    }

    public function currencyForCode(string $currencyCode): ?Currency
    {
        $code = strtoupper(trim($currencyCode));

        $currency = Currency::query()
            ->where('code', $code)
            ->first();

        if (! $currency || ! $this->supportsCurrency((int) $currency->id)) {
            return null;
        }

        return $currency;
    }

    public function primaryCurrency(): ?Currency
    {
        if ($this->relationLoaded('supportedCurrencies')) {
            return $this->supportedCurrencies->first(fn (Currency $currency): bool => (bool) $currency->pivot?->is_primary) ?? $this->currency;
        }

        return $this->supportedCurrencies()
            ->wherePivot('is_primary', true)
            ->first() ?? $this->currency;
    }
}
