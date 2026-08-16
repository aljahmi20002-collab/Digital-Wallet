<?php

namespace App\Models;

use Database\Factories\GiftCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GiftCard extends Model
{
    /** @use HasFactory<GiftCardFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'gift_card_template_id',
        'currency_id',
        'amount',
        'recipient_name',
        'recipient_email',
        'recipient_user_id',
        'sender_name',
        'message',
        'delivery_method',
        'scheduled_at',
        'delivered_at',
        'expires_at',
        'status',
        'is_active',
        'redeemed_by',
        'redeemed_wallet_id',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'float',
            'is_active'    => 'boolean',
            'scheduled_at' => 'datetime',
            'delivered_at' => 'datetime',
            'expires_at'   => 'datetime',
            'redeemed_at'  => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $card) {
            if (! $card->code) {
                do {
                    $card->code = 'DKGC-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
                } while (self::where('code', $card->code)->exists());
            }

            if (! $card->expires_at) {
                $card->expires_at = now()->addYear();
            }
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GiftCardTemplate::class, 'gift_card_template_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function redeemedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'redeemed_wallet_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null || $this->status === 'redeemed';
    }

    public function canBeRedeemed(): bool
    {
        return $this->is_active
            && ! $this->isRedeemed()
            && ! $this->isExpired()
            && in_array($this->status, ['pending', 'delivered'], true);
    }
}
