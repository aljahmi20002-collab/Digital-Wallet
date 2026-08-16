<?php

namespace Database\Seeders;

use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use App\Models\Currency;
use App\Models\WalletEarnPlan;
use Illuminate\Database\Seeder;

class WalletEarnPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencyIdsByCode = Currency::query()
            ->whereIn('code', ['USDT', 'USD', 'ETH'])
            ->pluck('id', 'code')
            ->all();

        $this->seedPlan(
            name: 'Flexible Earn',
            attributes: [
                'currency_id'      => null,
                'description'      => 'Any wallet. Start earning today with zero lock-in.',
                'minimum_amount'   => 10,
                'maximum_amount'   => 5000,
                'profit_rate'      => 1.5,
                'profit_type'      => WalletEarnProfitType::Percentage,
                'duration_value'   => 7,
                'duration_unit'    => 'days',
                'payout_frequency' => WalletEarnPayoutFrequency::Daily,
                'return_principal' => true,
                'auto_approve'     => true,
                'sort_order'       => 1,
                'is_featured'      => true,
                'plan_badge'       => 'MOST POPULAR',
                'status'           => true,
            ],
        );

        if (isset($currencyIdsByCode['USDT'])) {
            $this->seedPlan(
                name: 'Growth Stake',
                attributes: [
                    'currency_id'      => $currencyIdsByCode['USDT'],
                    'description'      => '30-day USDT plan with strong auto-approved returns.',
                    'minimum_amount'   => 100,
                    'maximum_amount'   => 10000,
                    'profit_rate'      => 3.2,
                    'profit_type'      => WalletEarnProfitType::Percentage,
                    'duration_value'   => 30,
                    'duration_unit'    => 'days',
                    'payout_frequency' => WalletEarnPayoutFrequency::Monthly,
                    'return_principal' => true,
                    'auto_approve'     => true,
                    'sort_order'       => 2,
                    'is_featured'      => false,
                    'plan_badge'       => 'HIGH YIELD',
                    'status'           => true,
                ],
            );
        }

        if (isset($currencyIdsByCode['USD'])) {
            $this->seedPlan(
                name: 'Premium USD',
                attributes: [
                    'currency_id'      => $currencyIdsByCode['USD'],
                    'description'      => '90-day USD plan with weekly payouts and premium yield.',
                    'minimum_amount'   => 500,
                    'maximum_amount'   => 50000,
                    'profit_rate'      => 5.5,
                    'profit_type'      => WalletEarnProfitType::Percentage,
                    'duration_value'   => 90,
                    'duration_unit'    => 'days',
                    'payout_frequency' => WalletEarnPayoutFrequency::Weekly,
                    'return_principal' => true,
                    'auto_approve'     => false,
                    'sort_order'       => 3,
                    'is_featured'      => true,
                    'plan_badge'       => 'PREMIUM PICK',
                    'status'           => true,
                ],
            );
        }

        if (isset($currencyIdsByCode['ETH'])) {
            $this->seedPlan(
                name: 'ETH Flex Stake',
                attributes: [
                    'currency_id'      => $currencyIdsByCode['ETH'],
                    'description'      => 'Flexible Ethereum staking with competitive 60-day returns.',
                    'minimum_amount'   => 0.05,
                    'maximum_amount'   => 10,
                    'profit_rate'      => 4.1,
                    'profit_type'      => WalletEarnProfitType::Percentage,
                    'duration_value'   => 60,
                    'duration_unit'    => 'days',
                    'payout_frequency' => WalletEarnPayoutFrequency::Weekly,
                    'return_principal' => true,
                    'auto_approve'     => true,
                    'sort_order'       => 4,
                    'is_featured'      => false,
                    'plan_badge'       => 'NEW',
                    'status'           => true,
                ],
            );
        }
    }

    /**
     * @param  array{
     *  currency_id: int|null,
     *  description: string|null,
     *  minimum_amount: float|int|string,
     *  maximum_amount: float|int|string|null,
     *  profit_rate: float|int|string,
     *  profit_type: WalletEarnProfitType,
     *  duration_value: int,
     *  duration_unit: string,
     *  payout_frequency: WalletEarnPayoutFrequency,
     *  return_principal: bool,
     *  auto_approve: bool,
     *  sort_order: int,
     *  status: bool
     * } $attributes
     */
    private function seedPlan(string $name, array $attributes): void
    {
        WalletEarnPlan::query()->firstOrCreate(['name' => $name], $attributes);
    }
}
