<?php

namespace Database\Factories;

use App\Models\WalletEarnReward;
use App\Models\WalletEarnStake;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletEarnReward>
 */
class WalletEarnRewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stake = WalletEarnStake::factory()->create();

        return [
            'wallet_earn_stake_id' => $stake->id,
            'user_id'              => $stake->user_id,
            'wallet_id'            => $stake->wallet_id,
            'currency_id'          => $stake->currency_id,
            'amount'               => 2,
            'payout_number'        => 1,
            'scheduled_at'         => now(),
            'paid_at'              => now(),
            'status'               => 'paid',
        ];
    }
}
