<?php

namespace App\Models\P2P;

use App\Enums\P2P\DisputeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'p2p_disputes';

    protected $fillable = [
        'order_id',
        'raised_by',
        'status',
        'reason',
        'resolution',
    ];

    protected $casts = [
        'status' => DisputeStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function raiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }
}
