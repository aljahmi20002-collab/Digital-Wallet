<?php

namespace App\Models;

use App\Enums\FixPctType;
use App\Enums\MethodType;
use App\Services\CurrencyConversionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class DepositMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_gateway_id',
        'logo',
        'name',
        'type',
        'method_code',
        'currency',
        'currency_symbol',
        'min_deposit',
        'max_deposit',
        'conversion_rate_live',
        'conversion_rate',
        'charge_type',
        'charge',
        'user_charge',
        'user_charge_type',
        'merchant_charge',
        'merchant_charge_type',
        'fields',
        'receive_payment_details',
        'status',
    ];

    protected $casts = [
        'type'                 => MethodType::class,
        'user_charge_type'     => FixPctType::class,
        'merchant_charge_type' => FixPctType::class,
        'payment_gateway_id'   => 'integer',
        'fields'               => 'array',
        'status'               => 'boolean',
        'min_deposit'          => 'float',
        'max_deposit'          => 'float',
        'conversion_rate_live' => 'boolean',
        'conversion_rate'      => 'float',
        'charge'               => 'float',
        'user_charge'          => 'double',
        'merchant_charge'      => 'double',
    ];

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function merchants(): BelongsToMany
    {
        return $this->belongsToMany(Merchant::class, 'merchant_deposit_methods')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeGetByCode($query, $code)
    {
        return $query->where('method_code', $code)->first();
    }

    public static function getByCode($code)
    {
        return Cache::rememberForever('deposit_method_'.$code, function () use ($code) {
            return self::where('method_code', $code)->first();
        });
    }

    public function getLogoAltAttribute(): string
    {
        if ($this->attributes['type'] !== MethodType::AUTOMATIC->value) {
            return $this->attributes['logo'] ?? '';
        }

        return $this->attributes['logo'] ?? $this->paymentGateway->logo;
    }

    /**
     * Get the conversion rate of the deposit method.
     *
     * This method will return the conversion rate of the deposit method. If the conversion rate is set to live, it will use the currency conversion service to convert the currency from the site's currency to the deposit method's currency. If the conversion rate is not set to live, it will return the fixed conversion rate.
     */
    public function getConversionRateAttribute(): string
    {
        // Check if the conversion rate is set to live.
        if ($this->attributes['conversion_rate_live'] && $this->attributes['type'] === 'auto') {
            // Get an instance of the currency conversion service.
            $currencyConversionService = new CurrencyConversionService;

            // Convert the currency from the site's currency to the deposit method's currency.
            // Format using site-wide decimal precision.
            $decimals = (int) (setting('site_decimal', 2));

            return number_format($currencyConversionService->convertCurrency(1, siteCurrency(), $this->attributes['currency']), $decimals, '.', '');
        }

        return $this->attributes['conversion_rate'];
    }

    /**
     * Get the appropriate charge based on current user type.
     * Returns user_charge for regular users, merchant_charge for merchants.
     * Falls back to original charge field if user-specific charges are not set.
     */
    public function getChargeAttribute(): float
    {
        $user = auth()->user();

        // If no authenticated user, return original charge
        if (! $user) {
            return (float) $this->attributes['charge'];
        }

        // Check if authenticated user is from User model (not Admin model)
        // Admin access should always return original charge
        if (! ($user instanceof User)) {
            return (float) $this->attributes['charge'];
        }

        // Check if user is merchant
        $isMerchant = $this->isUserMerchant($user);

        if ($isMerchant && $this->attributes['merchant_charge'] !== null) {
            return (float) $this->attributes['merchant_charge'];
        }

        if (! $isMerchant && $this->attributes['user_charge'] !== null) {
            return (float) $this->attributes['user_charge'];
        }

        // Fallback to original charge if user-specific charges are not set
        return (float) $this->attributes['charge'];
    }

    /**
     * Get the appropriate charge type based on current user type.
     * Returns user_charge_type for regular users, merchant_charge_type for merchants.
     * Falls back to original charge_type field if user-specific charge types are not set.
     */
    public function getChargeTypeAttribute(): string
    {
        $user = auth()->user();

        // If no authenticated user, return original charge_type
        if (! $user) {
            return $this->attributes['charge_type'];
        }

        // Check if authenticated user is from User model (not Admin model)
        // Admin access should always return original charge_type
        if (! ($user instanceof User)) {
            return $this->attributes['charge_type'];
        }

        // Check if user is merchant
        $isMerchant = $this->isUserMerchant($user);

        if ($isMerchant && $this->attributes['merchant_charge_type'] !== null) {
            return $this->attributes['merchant_charge_type'];
        }

        if (! $isMerchant && $this->attributes['user_charge_type'] !== null) {
            return $this->attributes['user_charge_type'];
        }

        // Fallback to original charge_type if user-specific charge types are not set
        return $this->attributes['charge_type'];
    }

    /**
     * Check if the given user is a merchant.
     * Uses the existing isMerchant() method from User model.
     */
    private function isUserMerchant($user): bool
    {
        return $user->isMerchant();
    }

    /**
     * Calculate the total charge for a given amount.
     * Now uses dynamic charge and charge_type based on user context.
     */
    public function calculateCharge(float $amount): float
    {
        $chargeType = $this->charge_type; // This will use the dynamic accessor
        $charge     = $this->charge; // This will use the dynamic accessor

        if ($chargeType === 'fixed') {
            return $charge;
        }

        if ($chargeType === 'percent') {
            return ($charge / 100) * $amount;
        }

        return 0.0;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Flush the cache when a deposit method is deleted.
        static::deleted(function ($model) {
            Cache::forget('deposit_method_'.$model->method_code);
        });

        // Flush the cache when a deposit method is updated.
        static::updated(function ($model) {
            Cache::forget('deposit_method_'.$model->method_code);
        });

        // Flush the cache when a deposit method is created.
        static::created(function ($model) {
            Cache::forget('deposit_method_'.$model->method_code);
        });
    }
}
