<?php

namespace Database\Factories;

use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use App\Models\WalletEarnPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletEarnPlan>
 */
class WalletEarnPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_id'      => null,
            'name'             => fake()->words(2, true).' Earn',
            'description'      => fake()->sentence(),
            'minimum_amount'   => 10,
            'maximum_amount'   => 1000,
            'profit_rate'      => 2.5,
            'profit_type'      => WalletEarnProfitType::Percentage,
            'duration_value'   => 7,
            'duration_unit'    => 'days',
            'payout_frequency' => WalletEarnPayoutFrequency::Daily,
            'return_principal' => true,
            'auto_approve'     => true,
            'sort_order'       => 0,
            'is_featured'      => false,
            'plan_badge'       => null,
            'status'           => true,
        ];
    }

    public function manualApproval(): static
    {
        return $this->state(fn (array $attributes): array => [
            'auto_approve' => false,
        ]);
    }
}
