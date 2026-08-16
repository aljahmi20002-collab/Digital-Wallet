<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlanFeature extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'feature_key',
        'feature_label',
        'feature_value',
        'feature_type',
        'reset_cycle',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isUnlimited(): bool
    {
        return strtolower($this->feature_value) === 'unlimited';
    }

    public function isEnabled(): bool
    {
        return in_array(strtolower($this->feature_value), ['enabled', '1', 'true', 'yes']);
    }

    public function isToggle(): bool
    {
        return $this->feature_type === 'toggle';
    }

    public function isLimit(): bool
    {
        return $this->feature_type === 'limit';
    }

    public function isQuota(): bool
    {
        return $this->feature_type === 'quota';
    }

    public function numericValue(): ?int
    {
        if ($this->isUnlimited()) {
            return null;
        }

        return is_numeric($this->feature_value) ? (int) $this->feature_value : null;
    }
}
