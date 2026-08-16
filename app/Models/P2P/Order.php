<?php

namespace App\Models\P2P;

use App\Enums\P2P\OrderStatus;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'p2p_orders';

    protected $fillable = [
        'offer_id',
        'maker_id',
        'taker_id',
        'wallet_id',
        'payment_method_id',
        'payer_payment_account_id',
        'receiver_payment_account_id',
        'payer_payment_account_snapshot',
        'receiver_payment_account_snapshot',
        'price',
        'amount',
        'maker_fee',
        'taker_fee',
        'total',
        'status',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'expired_at',
        'disputed_at',
        'expires_at',
        'trx_id',
        'remarks',
    ];

    protected $casts = [
        'price'                             => 'decimal:8',
        'amount'                            => 'decimal:8',
        'maker_fee'                         => 'decimal:8',
        'taker_fee'                         => 'decimal:8',
        'total'                             => 'decimal:8',
        'payer_payment_account_snapshot'    => 'array',
        'receiver_payment_account_snapshot' => 'array',
        'status'                            => OrderStatus::class,
        'paid_at'                           => 'datetime',
        'completed_at'                      => 'datetime',
        'cancelled_at'                      => 'datetime',
        'expired_at'                        => 'datetime',
        'disputed_at'                       => 'datetime',
        'expires_at'                        => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function taker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taker_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function payerPaymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payer_payment_account_id');
    }

    public function receiverPaymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'receiver_payment_account_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(OfferFeedback::class, 'order_id');
    }
}
