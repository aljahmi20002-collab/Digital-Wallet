<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentLinkStatus;
use App\Services\QRCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'merchant_id',
        'currency_id',
        'wallet_reference',
        'token',
        'title',
        'description',
        'amount',
        'min_amount',
        'max_amount',
        'merchant_fee',
        'status',
        'expires_at',
        'max_payments',
        'payments_count',
    ];

    protected $casts = [
        'amount'         => 'float',
        'min_amount'     => 'float',
        'max_amount'     => 'float',
        'merchant_fee'   => 'float',
        'status'         => PaymentLinkStatus::class,
        'expires_at'     => 'datetime',
        'max_payments'   => 'integer',
        'payments_count' => 'integer',
    ];

    protected $attributes = [
        'status'         => PaymentLinkStatus::ACTIVE->value,
        'payments_count' => 0,
    ];

    /**
     * Boot model: auto-generate a unique public token.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (self $link): void {
            if (! $link->token) {
                do {
                    $token = Str::lower(Str::random(24));
                } while (self::where('token', $token)->exists());

                $link->token = $token;
            }
        });
    }

    /**
     * Receiver / owner of the payment link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Merchant shop this link is branded as, when set.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Currency the link collects in.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * True when this link is branded against an approved merchant shop.
     */
    public function hasMerchantShop(): bool
    {
        return $this->merchant_id !== null;
    }

    /**
     * Display name shown on the public checkout — merchant business name
     * when linked to a shop, otherwise the receiver user's display name.
     */
    public function displayName(): string
    {
        if ($this->hasMerchantShop() && $this->merchant) {
            return (string) $this->merchant->business_name;
        }

        return (string) ($this->user?->name ?? __('Recipient'));
    }

    /**
     * Display logo path. Falls back to merchant business logo (if linked)
     * or the user avatar (if available). Returns null when neither is set
     * so the view can render its own placeholder.
     */
    public function displayLogo(): ?string
    {
        if ($this->hasMerchantShop() && $this->merchant) {
            return (string) $this->merchant->business_logo;
        }

        return $this->user?->avatar;
    }

    /**
     * The currency code charged by this link. Always derived from the
     * loaded currency relation so it stays consistent with merchant
     * overrides.
     */
    public function currencyCode(): string
    {
        return (string) ($this->currency?->code ?? '');
    }

    /**
     * Whether the link can presently accept payments.
     */
    public function isPayable(): bool
    {
        if ($this->status !== PaymentLinkStatus::ACTIVE) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->isMaxedOut()) {
            return false;
        }

        return true;
    }

    public function isActive(): bool
    {
        return $this->status === PaymentLinkStatus::ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function isMaxedOut(): bool
    {
        return $this->max_payments !== null && $this->payments_count >= $this->max_payments;
    }

    public function isOpenAmount(): bool
    {
        return $this->amount === null;
    }

    /**
     * Validate a chosen pay amount against the link's amount rules.
     * Returns null on success, or a translated error message.
     */
    public function validatePayAmount(float $amount): ?string
    {
        if ($amount <= 0) {
            return __('Amount must be greater than zero.');
        }

        if (! $this->isOpenAmount()) {
            // Fixed amount link — must match exactly.
            if (abs($amount - (float) $this->amount) > 0.00001) {
                return __('This payment link requires the exact amount.');
            }

            return null;
        }

        if ($this->min_amount !== null && $amount < (float) $this->min_amount) {
            return __('Amount must be at least :min.', ['min' => number_format((float) $this->min_amount, 2)]);
        }

        if ($this->max_amount !== null && $amount > (float) $this->max_amount) {
            return __('Amount must not exceed :max.', ['max' => number_format((float) $this->max_amount, 2)]);
        }

        return null;
    }

    public function recordSuccessfulPayment(): void
    {
        $this->increment('payments_count');
    }

    /**
     * Resolve the public checkout URL for this link.
     */
    public function publicUrl(): string
    {
        return route('payment-link.show', ['token' => $this->token]);
    }

    /**
     * Generate the scan-ready public checkout QR code.
     */
    public function qrCodeSvg(int $size = 220): string
    {
        return app(QRCodeService::class)->generate($this->publicUrl(), $size);
    }

    /**
     * Scope: filter by owner.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: only active links.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PaymentLinkStatus::ACTIVE);
    }

    /**
     * Scope: lookup by public token.
     */
    public function scopeByToken(Builder $query, string $token): Builder
    {
        return $query->where('token', $token);
    }
}
