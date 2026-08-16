<?php

namespace App\Models\P2P;

use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\OrderSide;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'p2p_offers';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'side',
        'price',
        'min_amount',
        'max_amount',
        'status',
        'payment_window_minutes',
        'terms',
    ];

    protected $casts = [
        'price'      => 'decimal:8',
        'min_amount' => 'decimal:8',
        'max_amount' => 'decimal:8',
        'side'       => OrderSide::class,
        'status'     => OfferStatus::class,
    ];

    public function setTermsAttribute(?string $value): void
    {
        $this->attributes['terms'] = self::normalizeTerms($value);
    }

    public function getTermsTextAttribute(): ?string
    {
        return self::normalizeTerms($this->attributes['terms'] ?? null);
    }

    public static function normalizeTerms(?string $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $text = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $text);
        $text = preg_replace('/<\/p\s*>/i', "\n\n", $text)  ?? $text;
        $text = preg_replace('/<\/li\s*>/i', "\n", $text)   ?? $text;
        $text = preg_replace('/<li\b[^>]*>/i', '• ', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\xc2\xa0", "\r\n", "\r"], [' ', "\n", "\n"], $text);
        $text = preg_replace('/[^\S\n]+/', ' ', $text)  ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text) !== '' ? trim($text) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'p2p_offer_payment_method', 'offer_id', 'payment_method_id')
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'offer_id');
    }

    public function promotion(): HasOne
    {
        return $this->hasOne(OfferPromotion::class, 'offer_id');
    }

    public function promotionPurchases(): HasMany
    {
        return $this->hasMany(OfferPromotionPurchase::class, 'offer_id');
    }
}
