<?php

namespace App\Models;

use Database\Factories\WalletEarnRewardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletEarnReward extends Model
{
    /** @use HasFactory<WalletEarnRewardFactory> */
    use HasFactory;

    protected $fillable = [
        'wallet_earn_stake_id',
        'user_id',
        'wallet_id',
        'currency_id',
        'transaction_id',
        'amount',
        'payout_number',
        'scheduled_at',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'float',
            'payout_number' => 'integer',
            'scheduled_at'  => 'datetime',
            'paid_at'       => 'datetime',
        ];
    }

    public function stake(): BelongsTo
    {
        return $this->belongsTo(WalletEarnStake::class, 'wallet_earn_stake_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
