<?php

namespace App\Models;

use App\Enums\AgentOperationType;
use App\Enums\TrxStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'agent_id',
        'customer_user_id',
        'currency_id',
        'agent_wallet_id',
        'customer_wallet_id',
        'commission_rule_id',
        'agent_transaction_id',
        'customer_transaction_id',
        'commission_transaction_id',
        'type',
        'amount',
        'commission_amount',
        'status',
        'note',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'type'              => AgentOperationType::class,
        'status'            => TrxStatus::class,
        'amount'            => 'float',
        'commission_amount' => 'float',
        'metadata'          => 'array',
        'processed_at'      => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function agentWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'agent_wallet_id');
    }

    public function customerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'customer_wallet_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(AgentCommissionRule::class);
    }

    public function agentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'agent_transaction_id');
    }

    public function customerTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'customer_transaction_id');
    }

    public function commissionTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'commission_transaction_id');
    }
}
