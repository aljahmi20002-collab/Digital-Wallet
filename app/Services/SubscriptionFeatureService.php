<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSubscription;

class SubscriptionFeatureService
{
    /**
     * Check if a user has an active subscription.
     */
    public function hasActiveSubscription(User $user): bool
    {
        return $user->activeSubscription !== null;
    }

    /**
     * Get the user's active subscription with plan and features loaded.
     */
    public function getActiveSubscription(User $user): ?UserSubscription
    {
        return $user->subscriptions()
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->with(['plan.features'])
            ->latest()
            ->first();
    }

    /**
     * Check if the user can use a specific feature key.
     *
     * Returns true if:
     * - The user has an active subscription
     * - The plan has this feature with value 'enabled', 'unlimited', or any positive number
     */
    public function canUse(User $user, string $featureKey): bool
    {
        $subscription = $this->getActiveSubscription($user);

        if (! $subscription) {
            return false;
        }

        $feature = $subscription->plan->features
            ->firstWhere('feature_key', $featureKey);

        if (! $feature) {
            return false;
        }

        if ($feature->isToggle()) {
            return $feature->isEnabled();
        }

        // For limits/quotas, any non-zero value or 'unlimited' grants access
        if ($feature->isUnlimited()) {
            return true;
        }

        return ($feature->numericValue() ?? 0) > 0;
    }

    /**
     * Get the raw limit value for a feature.
     * Returns null for unlimited, 0 if not found, or the integer limit.
     */
    public function getLimit(User $user, string $featureKey): ?int
    {
        $subscription = $this->getActiveSubscription($user);

        if (! $subscription) {
            return 0;
        }

        $feature = $subscription->plan->features
            ->firstWhere('feature_key', $featureKey);

        if (! $feature) {
            return 0;
        }

        if ($feature->isUnlimited()) {
            return null; // null means unlimited
        }

        return $feature->numericValue() ?? 0;
    }

    /**
     * Get feature value as a display string (e.g. '5', 'Unlimited', 'Enabled').
     */
    public function getFeatureDisplay(User $user, string $featureKey): string
    {
        $subscription = $this->getActiveSubscription($user);

        if (! $subscription) {
            return __('No Plan');
        }

        $feature = $subscription->plan->features
            ->firstWhere('feature_key', $featureKey);

        if (! $feature) {
            return __('Not included');
        }

        if ($feature->isUnlimited()) {
            return __('Unlimited');
        }

        if ($feature->isToggle()) {
            return $feature->isEnabled() ? __('Enabled') : __('Disabled');
        }

        return (string) $feature->feature_value;
    }
}
