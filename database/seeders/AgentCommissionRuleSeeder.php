<?php

namespace Database\Seeders;

use App\Enums\AgentCommissionRuleType;
use App\Enums\AgentOperationType;
use App\Models\AgentCommissionRule;
use Illuminate\Database\Seeder;

class AgentCommissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name'             => 'Global Cash-In Starter Rate',
                'applies_globally' => true,
                'priority'         => 100,
                'operation_type'   => AgentOperationType::CASH_IN->value,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.25,
                'fixed_amount'     => 0,
                'min_amount'       => 0,
                'max_amount'       => 1000,
                'min_commission'   => 0.10,
                'max_commission'   => 3,
            ],
            [
                'name'             => 'Global Cash-Out Counter Rate',
                'applies_globally' => true,
                'priority'         => 100,
                'operation_type'   => AgentOperationType::CASH_OUT->value,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.45,
                'fixed_amount'     => 0,
                'min_amount'       => 0,
                'max_amount'       => 1000,
                'min_commission'   => 0.20,
                'max_commission'   => 5,
            ],
            [
                'name'             => 'High Volume Cash-In Capped Rate',
                'applies_globally' => true,
                'priority'         => 110,
                'operation_type'   => AgentOperationType::CASH_IN->value,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.15,
                'fixed_amount'     => 0,
                'min_amount'       => 1000.01,
                'max_amount'       => null,
                'min_commission'   => 1,
                'max_commission'   => 10,
            ],
            [
                'name'             => 'High Volume Cash-Out Capped Rate',
                'applies_globally' => true,
                'priority'         => 110,
                'operation_type'   => AgentOperationType::CASH_OUT->value,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.25,
                'fixed_amount'     => 0,
                'min_amount'       => 1000.01,
                'max_amount'       => null,
                'min_commission'   => 1,
                'max_commission'   => 15,
            ],
            [
                'name'             => 'Micro Transaction Fixed Counter Fee',
                'applies_globally' => false,
                'priority'         => 80,
                'operation_type'   => 'all',
                'calculation_type' => AgentCommissionRuleType::FIXED,
                'percentage_rate'  => 0,
                'fixed_amount'     => 0.15,
                'min_amount'       => 0,
                'max_amount'       => 50,
                'min_commission'   => null,
                'max_commission'   => null,
            ],
            [
                'name'             => 'Urban Premium Cash-Out Rate',
                'applies_globally' => false,
                'priority'         => 70,
                'operation_type'   => AgentOperationType::CASH_OUT->value,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.55,
                'fixed_amount'     => 0,
                'min_amount'       => 0,
                'max_amount'       => null,
                'min_commission'   => 0.25,
                'max_commission'   => 20,
            ],
            [
                'name'             => 'Rural Access Any Operation Bonus',
                'applies_globally' => false,
                'priority'         => 75,
                'operation_type'   => 'all',
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.50,
                'fixed_amount'     => 0,
                'min_amount'       => 0,
                'max_amount'       => null,
                'min_commission'   => 0.20,
                'max_commission'   => 25,
            ],
            [
                'name'             => 'New Agent Launch Incentive',
                'applies_globally' => false,
                'priority'         => 60,
                'operation_type'   => 'all',
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE,
                'percentage_rate'  => 0.65,
                'fixed_amount'     => 0,
                'min_amount'       => 0,
                'max_amount'       => 500,
                'min_commission'   => 0.25,
                'max_commission'   => 8,
            ],
        ];

        foreach ($rules as $rule) {
            AgentCommissionRule::query()->updateOrCreate(
                ['name' => $rule['name']],
                array_merge([
                    'status'          => true,
                    'currency_id'     => null,
                    'effective_from'  => null,
                    'effective_until' => null,
                ], $rule)
            );
        }
    }
}
