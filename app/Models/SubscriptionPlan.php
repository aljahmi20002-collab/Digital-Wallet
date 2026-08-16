<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'trial_days',
        'grace_days',
        'is_featured',
        'plan_badge',
        'auto_renew_default',
        'cancellation_policy',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'trial_days'         => 'integer',
            'grace_days'         => 'integer',
            'is_featured'        => 'boolean',
            'auto_renew_default' => 'boolean',
            'status'             => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(SubscriptionPlanPrice::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(SubscriptionPlanFeature::class)->orderBy('sort_order');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->whereIn('status', ['active', 'trial', 'grace']);
    }

    public function isFree(): bool
    {
        return $this->prices->every(fn ($p) => $p->price <= 0);
    }

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    public function hasGracePeriod(): bool
    {
        return $this->grace_days > 0;
    }

    public function getFeatureValue(string $key): ?string
    {
        return $this->features->firstWhere('feature_key', $key)?->feature_value;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByBillingCycle($query, string $cycle)
    {
        return $query->whereHas('prices', fn ($q) => $q->where('billing_cycle', $cycle));
    }
}
