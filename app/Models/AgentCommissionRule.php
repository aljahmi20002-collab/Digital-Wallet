<?php

namespace App\Models;

use App\Enums\AgentCommissionRuleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentCommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'applies_globally',
        'priority',
        'operation_type',
        'currency_id',
        'min_amount',
        'max_amount',
        'calculation_type',
        'percentage_rate',
        'fixed_amount',
        'min_commission',
        'max_commission',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'status'           => 'boolean',
        'applies_globally' => 'boolean',
        'priority'         => 'integer',
        'min_amount'       => 'float',
        'max_amount'       => 'float',
        'calculation_type' => AgentCommissionRuleType::class,
        'percentage_rate'  => 'float',
        'fixed_amount'     => 'float',
        'min_commission'   => 'float',
        'max_commission'   => 'float',
        'effective_from'   => 'datetime',
        'effective_until'  => 'datetime',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(AgentOperation::class, 'commission_rule_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AgentCommissionRuleAssignment::class);
    }
}
