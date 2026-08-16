<?php

namespace App\Services;

use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Models\P2P\Offer;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Wallet;
use Carbon\Carbon;

/**
 * Computes real usage per subscription-plan feature for a given user.
 *
 * Each entry in the returned array is keyed by feature_key and contains:
 *   - used:         numeric value already consumed
 *   - limit:        numeric cap parsed from the feature value, or null when unlimited
 *   - is_currency:  whether the value should be formatted as a currency amount
 *   - is_unlimited: true when the feature is marked unlimited
 *   - percentage:   computed bar width (0-100), capped at 100
 *   - reset_label:  human-readable refresh window ("today", "this month", etc.)
 */
class PlanUsageService
{
    /**
     * @return array<string, array{used: float, limit: ?float, is_currency: bool, is_unlimited: bool, percentage: int, reset_label: ?string}>
     */
    public function build(User $user, ?SubscriptionPlan $plan): array
    {
        if (! $plan) {
            return [];
        }

        $usage = [];

        foreach ($plan->features as $feature) {
            if ($feature->isToggle()) {
                continue;
            }

            $rawValue    = (string) $feature->feature_value;
            $isUnlimited = $feature->isUnlimited();
            $isCurrency  = str_contains($rawValue, '$') || str_contains($rawValue, '€') || str_contains($rawValue, '£');

            // Skip pure text labels (no digits, not unlimited)
            if (! $isUnlimited && preg_match('/\d/', $rawValue) !== 1) {
                continue;
            }

            $limit = $isUnlimited ? null : $this->parseNumeric($rawValue);
            $used  = $this->computeUsed($user, $feature->feature_key);

            if ($isUnlimited) {
                $percentage = $used > 0 ? 15 : 8;
            } elseif ($limit !== null && $limit > 0 && $used >= 0) {
                $percentage = (int) min(100, round(($used / $limit) * 100));
            } else {
                $percentage = 0;
            }

            $usage[$feature->feature_key] = [
                'used'         => (float) $used,
                'limit'        => $limit,
                'is_currency'  => $isCurrency,
                'is_unlimited' => $isUnlimited,
                'percentage'   => $percentage,
                'reset_label'  => $this->resetLabel($feature->feature_key),
            ];
        }

        return $usage;
    }

    private function computeUsed(User $user, string $featureKey): float
    {
        $completed  = TrxStatus::COMPLETED->value;
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        return match ($featureKey) {
            'daily_transaction_limit' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('status', $completed)
                ->where('created_at', '>=', $today)
                ->count(),

            'monthly_transaction_limit' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('status', $completed)
                ->where('created_at', '>=', $monthStart)
                ->count(),

            'monthly_withdraw_limit' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('trx_type', TrxType::WITHDRAW->value)
                ->where('status', $completed)
                ->where('created_at', '>=', $monthStart)
                ->sum('amount'),

            'monthly_send_limit' => (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('trx_type', TrxType::SEND_MONEY->value)
                ->where('status', $completed)
                ->where('created_at', '>=', $monthStart)
                ->sum('amount'),

            'wallet_balance_cap' => (float) Wallet::query()
                ->where('user_id', $user->id)
                ->sum('balance'),

            'virtual_card_limit', 'virtual_cards' => (float) VirtualCard::query()
                ->whereIn('wallet_id', Wallet::query()->where('user_id', $user->id)->pluck('id'))
                ->count(),

            'p2p_ad_limit' => (float) Offer::query()
                ->where('user_id', $user->id)
                ->count(),

            default => 0.0,
        };
    }

    private function resetLabel(string $featureKey): ?string
    {
        return match (true) {
            str_starts_with($featureKey, 'daily_')   => __('Resets today'),
            str_starts_with($featureKey, 'monthly_') => __('This month'),
            $featureKey === 'wallet_balance_cap'     => __('Across all wallets'),
            default                                  => null,
        };
    }

    private function parseNumeric(string $value): ?float
    {
        $cleaned = preg_replace('/[^0-9.]/', '', $value);

        return $cleaned !== '' && $cleaned !== '.' ? (float) $cleaned : null;
    }
}
