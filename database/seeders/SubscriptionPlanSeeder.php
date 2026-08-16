<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPlan(
            name: 'Starter',
            attributes: [
                'slug'                => 'starter',
                'description'         => 'Everything you need to get started. Core wallet features with sensible daily limits.',
                'trial_days'          => 0,
                'grace_days'          => 3,
                'is_featured'         => false,
                'plan_badge'          => 'FREE',
                'auto_renew_default'  => true,
                'cancellation_policy' => 'end_of_period',
                'sort_order'          => 1,
                'status'              => true,
            ],
            basePrice: 0,
            halfYearlyDiscount: null,
            yearlyDiscount: null,
            features: [
                ['feature_key' => 'deposit_money',           'feature_label' => 'Deposit Money',           'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 1],
                ['feature_key' => 'withdraw_money',          'feature_label' => 'Withdraw Money',          'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 2],
                ['feature_key' => 'send_money',              'feature_label' => 'Send Money',              'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 3],
                ['feature_key' => 'request_money',           'feature_label' => 'Request Money',           'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 4],
                ['feature_key' => 'vouchers',                'feature_label' => 'Vouchers & Cashback',     'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 5],
                ['feature_key' => 'transaction_history',     'feature_label' => 'Transaction History',     'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 6],
                ['feature_key' => 'two_factor_auth',         'feature_label' => 'Two-Factor Auth (2FA)',   'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 7],
                ['feature_key' => 'push_notifications',      'feature_label' => 'Push Notifications',      'feature_value' => 'enabled', 'feature_type' => 'toggle', 'sort_order' => 8],
                ['feature_key' => 'daily_transaction_limit', 'feature_label' => 'Daily Transactions',      'feature_value' => '5',       'feature_type' => 'limit',  'sort_order' => 9],
                ['feature_key' => 'monthly_withdraw_limit',  'feature_label' => 'Monthly Withdrawal',      'feature_value' => '$500',    'feature_type' => 'limit',  'sort_order' => 10],
                ['feature_key' => 'wallet_balance_cap',      'feature_label' => 'Max Wallet Balance',      'feature_value' => '$1,000',  'feature_type' => 'limit',  'sort_order' => 11],
                ['feature_key' => 'support_priority',        'feature_label' => 'Customer Support',        'feature_value' => 'Standard', 'feature_type' => 'limit', 'sort_order' => 12],
            ],
        );

        $this->seedPlan(
            name: 'Pro',
            attributes: [
                'slug'                => 'pro',
                'description'         => 'Unlock advanced features — exchange, P2P trading, virtual cards, and higher limits.',
                'trial_days'          => 7,
                'grace_days'          => 5,
                'is_featured'         => false,
                'plan_badge'          => 'POPULAR',
                'auto_renew_default'  => true,
                'cancellation_policy' => 'end_of_period',
                'sort_order'          => 2,
                'status'              => true,
            ],
            basePrice: 9.99,
            halfYearlyDiscount: 10,
            yearlyDiscount: 20,
            features: [
                ['feature_key' => 'deposit_money',           'feature_label' => 'Deposit Money',           'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 1],
                ['feature_key' => 'withdraw_money',          'feature_label' => 'Withdraw Money',          'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 2],
                ['feature_key' => 'send_money',              'feature_label' => 'Send Money',              'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 3],
                ['feature_key' => 'request_money',           'feature_label' => 'Request Money',           'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 4],
                ['feature_key' => 'exchange_money',          'feature_label' => 'Currency Exchange',       'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 5],
                ['feature_key' => 'p2p_marketplace',         'feature_label' => 'P2P Marketplace',         'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 6],
                ['feature_key' => 'virtual_card',            'feature_label' => 'Virtual Cards',           'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 7],
                ['feature_key' => 'wallet_earn',             'feature_label' => 'Wallet Earn (Staking)',   'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 8],
                ['feature_key' => 'referral_program',        'feature_label' => 'Referral Program',        'feature_value' => 'enabled',  'feature_type' => 'toggle', 'sort_order' => 9],
                ['feature_key' => 'daily_transaction_limit', 'feature_label' => 'Daily Transactions',      'feature_value' => '50',       'feature_type' => 'limit',  'sort_order' => 10],
                ['feature_key' => 'monthly_withdraw_limit',  'feature_label' => 'Monthly Withdrawal',      'feature_value' => '$10,000',  'feature_type' => 'limit',  'sort_order' => 11],
                ['feature_key' => 'support_priority',        'feature_label' => 'Priority Support',        'feature_value' => 'Priority', 'feature_type' => 'limit',  'sort_order' => 12],
            ],
        );

        $this->seedPlan(
            name: 'Enterprise',
            attributes: [
                'slug'                => 'enterprise',
                'description'         => 'Full platform access with unlimited transactions, API integration, and dedicated support.',
                'trial_days'          => 14,
                'grace_days'          => 7,
                'is_featured'         => true,
                'plan_badge'          => 'BEST VALUE',
                'auto_renew_default'  => true,
                'cancellation_policy' => 'end_of_period',
                'sort_order'          => 3,
                'status'              => true,
            ],
            basePrice: 29.99,
            halfYearlyDiscount: 10,
            yearlyDiscount: 20,
            features: [
                ['feature_key' => 'deposit_money',           'feature_label' => 'Deposit Money',           'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 1],
                ['feature_key' => 'withdraw_money',          'feature_label' => 'Withdraw Money',          'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 2],
                ['feature_key' => 'send_money',              'feature_label' => 'Send Money',              'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 3],
                ['feature_key' => 'exchange_money',          'feature_label' => 'Currency Exchange',       'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 4],
                ['feature_key' => 'p2p_marketplace',         'feature_label' => 'P2P Marketplace',         'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 5],
                ['feature_key' => 'virtual_card',            'feature_label' => 'Virtual Cards',           'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 6],
                ['feature_key' => 'wallet_earn',             'feature_label' => 'Wallet Earn (Staking)',   'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 7],
                ['feature_key' => 'payment_link',            'feature_label' => 'Payment Links',           'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 8],
                ['feature_key' => 'bank_transfer',           'feature_label' => 'Bank Transfer Payouts',   'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 9],
                ['feature_key' => 'api_access',              'feature_label' => 'API Access',              'feature_value' => 'enabled',   'feature_type' => 'toggle', 'sort_order' => 10],
                ['feature_key' => 'daily_transaction_limit', 'feature_label' => 'Daily Transactions',      'feature_value' => 'unlimited', 'feature_type' => 'limit',  'sort_order' => 11],
                ['feature_key' => 'support_priority',        'feature_label' => 'Dedicated Support',       'feature_value' => 'Dedicated', 'feature_type' => 'limit',  'sort_order' => 12],
            ],
        );
    }

    /**
     * @param array<string, mixed>                                                                                                        $attributes
     * @param array<int, array{feature_key: string, feature_label: string, feature_value: string, feature_type: string, sort_order: int}> $features
     */
    private function seedPlan(string $name, array $attributes, float $basePrice, ?int $halfYearlyDiscount, ?int $yearlyDiscount, array $features): void
    {
        $plan = SubscriptionPlan::query()->updateOrCreate(
            ['name' => $name],
            $attributes
        );

        // Always sync prices
        $plan->prices()->delete();

        $halfPrice   = $basePrice * 6;
        $yearlyPrice = $basePrice * 12;

        if ($halfYearlyDiscount !== null) {
            $halfPrice = round($halfPrice * (1 - $halfYearlyDiscount / 100), 2);
        }

        if ($yearlyDiscount !== null) {
            $yearlyPrice = round($yearlyPrice * (1 - $yearlyDiscount / 100), 2);
        }

        $plan->prices()->createMany([
            ['billing_cycle' => BillingCycle::Monthly->value,    'price' => $basePrice,               'discount' => null],
            ['billing_cycle' => BillingCycle::HalfYearly->value, 'price' => round($halfPrice, 2),    'discount' => $halfYearlyDiscount],
            ['billing_cycle' => BillingCycle::Yearly->value,     'price' => round($yearlyPrice, 2),  'discount' => $yearlyDiscount],
        ]);

        // Always sync features
        $plan->features()->delete();

        foreach ($features as $feature) {
            $plan->features()->create($feature);
        }
    }
}
