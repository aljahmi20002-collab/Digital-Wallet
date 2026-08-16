<?php

namespace App\Models;

use App\Enums\MobileRechargeStatus;
use Database\Factories\MobileRechargeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileRecharge extends Model
{
    /** @use HasFactory<MobileRechargeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_id',
        'phone_number',
        'operator',
        'country',
        'amount',
        'fee',
        'total_amount',
        'currency',
        'provider',
        'provider_reference',
        'status',
        'failure_reason',
        'metadata',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'float',
            'fee'          => 'float',
            'total_amount' => 'float',
            'metadata'     => 'array',
            'processed_at' => 'datetime',
            'status'       => MobileRechargeStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function providerPlugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'provider', 'code');
    }
}
