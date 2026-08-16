<?php

namespace Database\Factories;

use App\Constants\CurrencyType;
use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use App\Enums\WalletEarnStatus;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletEarnPlan;
use App\Models\WalletEarnStake;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletEarnStake>
 */
class WalletEarnStakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user     = User::factory()->create();
        $currency = Currency::query()->first() ?? Currency::query()->create([
            'name'          => 'US Dollar',
            'code'          => 'USD',
            'symbol'        => '$',
            'type'          => CurrencyType::FIAT,
            'exchange_rate' => 1,
            'rate_live'     => false,
            'default'       => true,
            'status'        => true,
        ]);
        $wallet = Wallet::query()->create([
            'currency_id' => $currency->id,
            'user_id'     => $user->id,
            'uuid'        => (string) Str::uuid(),
            'balance'     => 1000,
            'status'      => true,
        ]);
        $plan = WalletEarnPlan::factory()->create(['currency_id' => null]);

        return [
            'user_id'             => $user->id,
            'wallet_earn_plan_id' => $plan->id,
            'wallet_id'           => $wallet->id,
            'currency_id'         => $currency->id,
            'plan_name'           => $plan->name,
            'principal_amount'    => 100,
            'profit_rate'         => 2,
            'profit_type'         => WalletEarnProfitType::Percentage,
            'duration_value'      => 7,
            'duration_unit'       => 'days',
            'payout_frequency'    => WalletEarnPayoutFrequency::Daily,
            'return_principal'    => true,
            'expected_profit'     => 14,
            'paid_profit'         => 0,
            'total_payouts'       => 7,
            'payouts_made'        => 0,
            'status'              => WalletEarnStatus::Active,
            'starts_at'           => now(),
            'next_payout_at'      => now()->addDay(),
            'matures_at'          => now()->addDays(7),
        ];
    }
}
