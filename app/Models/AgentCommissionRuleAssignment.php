<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommissionRuleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'agent_commission_rule_id',
        'operation_type',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => 'integer',
        'status'   => 'boolean',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AgentCommissionRule::class, 'agent_commission_rule_id');
    }
}
