<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'billing_cycle',
        'status',
        'started_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'grace_ends_at',
        'cancelled_at',
        'cancelled_by_admin',
        'auto_renew',
        'amount_paid',
        'currency_code',
        'wallet_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle'        => BillingCycle::class,
            'status'               => SubscriptionStatus::class,
            'started_at'           => 'datetime',
            'trial_ends_at'        => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end'   => 'datetime',
            'grace_ends_at'        => 'datetime',
            'cancelled_at'         => 'datetime',
            'cancelled_by_admin'   => 'boolean',
            'auto_renew'           => 'boolean',
            'amount_paid'          => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::Expired;
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled;
    }

    public function hasExpired(): bool
    {
        if ($this->billing_cycle?->isLifetime()) {
            return false;
        }

        return $this->current_period_end !== null && now()->isAfter($this->current_period_end);
    }

    public function isInGracePeriod(): bool
    {
        return $this->grace_ends_at !== null
            && now()->isBefore($this->grace_ends_at)
            && $this->hasExpired();
    }

    public function daysRemaining(): ?int
    {
        if ($this->billing_cycle?->isLifetime()) {
            return null;
        }

        if (! $this->current_period_end) {
            return null;
        }

        $days = (int) now()->diffInDays($this->current_period_end, false);

        return max(0, $days);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SubscriptionStatus::Active->value,
            SubscriptionStatus::Trial->value,
            SubscriptionStatus::Grace->value,
        ]);
    }
}
