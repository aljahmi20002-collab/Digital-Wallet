<?php

namespace App\Models;

use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use Database\Factories\WalletEarnPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletEarnPlan extends Model
{
    /** @use HasFactory<WalletEarnPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'name',
        'icon',
        'description',
        'minimum_amount',
        'maximum_amount',
        'profit_rate',
        'profit_type',
        'duration_value',
        'duration_unit',
        'payout_frequency',
        'return_principal',
        'auto_approve',
        'sort_order',
        'is_featured',
        'plan_badge',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_amount'   => 'float',
            'maximum_amount'   => 'float',
            'profit_rate'      => 'float',
            'profit_type'      => WalletEarnProfitType::class,
            'duration_value'   => 'integer',
            'payout_frequency' => WalletEarnPayoutFrequency::class,
            'return_principal' => 'boolean',
            'auto_approve'     => 'boolean',
            'sort_order'       => 'integer',
            'is_featured'      => 'boolean',
            'plan_badge'       => 'string',
            'status'           => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class)->withDefault([
            'name'   => __('All Currencies'),
            'code'   => __('ALL'),
            'symbol' => '',
        ]);
    }

    public function stakes(): HasMany
    {
        return $this->hasMany(WalletEarnStake::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function supportsCurrency(int $currencyId): bool
    {
        return $this->currency_id === null || (int) $this->currency_id === $currencyId;
    }

    public function durationLabel(): string
    {
        return $this->duration_value.' '.str($this->duration_unit)->headline()->toString();
    }

    public function amountRangeLabel(): string
    {
        $minimum = number_format((float) $this->minimum_amount, (int) setting('site_decimal', 2));

        if ($this->maximum_amount === null) {
            return __('From :minimum', ['minimum' => $minimum]);
        }

        return $minimum.' - '.number_format((float) $this->maximum_amount, (int) setting('site_decimal', 2));
    }

    public function isHighlighted(): bool
    {
        return $this->is_featured || filled($this->planBadgeText());
    }

    public function planBadgeLabel(): ?string
    {
        if (! $this->isHighlighted()) {
            return null;
        }

        return $this->planBadgeText() ?: __('Featured');
    }

    public function planBadgeText(): ?string
    {
        $badgeText = trim((string) $this->plan_badge);

        return $badgeText !== '' ? $badgeText : null;
    }

    public function highlightClass(): string
    {
        if ($this->is_featured) {
            return 'high';
        }

        if ($this->planBadgeText()) {
            return 'medium';
        }

        return 'none';
    }
}
