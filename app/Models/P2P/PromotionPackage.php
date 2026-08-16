<?php

declare(strict_types=1);

namespace App\Models\P2P;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'p2p_promotion_packages';

    protected $fillable = [
        'name',
        'price',
        'base_currency',
        'duration_minutes',
        'sort_order',
        'visibility',
        'billing_type',
        'daily_price',
        'per_trade_fee',
        'auto_renew_allowed',
        'features',
        'accent_color',
        'search_priority',
        'applies_to',
        'allowed_categories',
        'max_active_per_user',
        'max_impressions',
        'cooldown_after_expiry_minutes',
        'status',
    ];

    protected $casts = [
        'price'                         => 'float',
        'daily_price'                   => 'float',
        'per_trade_fee'                 => 'float',
        'duration_minutes'              => 'integer',
        'sort_order'                    => 'integer',
        'auto_renew_allowed'            => 'boolean',
        'features'                      => 'array',
        'allowed_categories'            => 'array',
        'search_priority'               => 'integer',
        'max_active_per_user'           => 'integer',
        'max_impressions'               => 'integer',
        'cooldown_after_expiry_minutes' => 'integer',
        'status'                        => 'boolean',
    ];

    public function effectiveBasePrice(): float
    {
        $billingType = strtoupper(trim((string) ($this->billing_type ?? 'FIXED')));

        if ($billingType === 'DAILY_PRICE') {
            $dailyPrice = (float) ($this->daily_price ?? 0);
            if ($dailyPrice <= 0) {
                return 0.0;
            }

            $minutes = (int) ($this->duration_minutes ?? 0);
            $days    = (int) ceil(max(1, $minutes) / 1440);

            return $dailyPrice * $days;
        }

        if ($billingType === 'PER_TRADE_FEE') {
            return (float) ($this->per_trade_fee ?? 0);
        }

        return (float) ($this->price ?? 0);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(OfferPromotion::class, 'package_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(OfferPromotionPurchase::class, 'package_id');
    }
}
