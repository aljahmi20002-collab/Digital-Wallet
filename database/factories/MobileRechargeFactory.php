<?php

namespace Database\Factories;

use App\Constants\CurrencyType;
use App\Enums\MobileRechargeStatus;
use App\Models\Currency;
use App\Models\MobileRecharge;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MobileRecharge>
 */
class MobileRechargeFactory extends Factory
{
    protected $model = MobileRecharge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user     = User::factory()->create();
        $currency = Currency::query()->firstOrCreate(
            ['code' => 'BDT'],
            [
                'name'          => 'Bangladeshi Taka',
                'symbol'        => 'BDT',
                'type'          => CurrencyType::FIAT,
                'exchange_rate' => 1,
                'rate_live'     => false,
                'default'       => true,
                'status'        => true,
            ],
        );
        $wallet = Wallet::query()->create([
            'currency_id' => $currency->id,
            'user_id'     => $user->id,
            'uuid'        => (string) Str::uuid(),
            'balance'     => 1000,
            'status'      => true,
        ]);

        return [
            'user_id'      => $user->id,
            'wallet_id'    => $wallet->id,
            'phone_number' => '+1555'.fake()->numerify('#######'),
            'operator'     => fake()->randomElement(['Grameenphone', 'Robi', 'Banglalink']),
            'amount'       => 100,
            'fee'          => 0,
            'total_amount' => 100,
            'currency'     => 'BDT',
            'provider'     => 'sandbox',
            'status'       => MobileRechargeStatus::PENDING,
            'metadata'     => [],
        ];
    }
}
