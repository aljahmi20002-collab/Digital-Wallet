<?php

declare(strict_types=1);

namespace App\Models\P2P;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferPromotionPurchase extends Model
{
    use HasFactory;

    protected $table = 'p2p_offer_promotion_purchases';

    protected $fillable = [
        'offer_id',
        'user_id',
        'package_id',
        'wallet_id',
        'trx_id',
        'base_price',
        'base_currency',
        'paid_amount',
        'paid_currency',
        'exchange_rate',
        'duration_minutes',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'base_price'       => 'float',
        'paid_amount'      => 'float',
        'exchange_rate'    => 'float',
        'duration_minutes' => 'integer',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PromotionPackage::class, 'package_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
