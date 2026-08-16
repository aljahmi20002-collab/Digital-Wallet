<?php

namespace Database\Seeders;

use App\Models\DepositMethod;
use App\Models\PaymentGateway;
use App\Models\WithdrawMethod;
use Illuminate\Database\Seeder;

/**
 * Seed Bitnob's deposit + withdraw methods. The PaymentGateway row itself is
 * seeded via PaymentGatewaySeeder; this only registers the user-facing
 * methods that point at it.
 */
class BitnobSeeder extends Seeder
{
    public function run(): void
    {
        $gateway = PaymentGateway::where('code', 'bitnob')->first();

        if (! $gateway) {
            $this->command->warn('Bitnob payment gateway row missing — run PaymentGatewaySeeder first.');

            return;
        }

        $this->seedDepositMethod($gateway->id);
        $this->seedWithdrawMethod($gateway->id);
    }

    protected function seedDepositMethod(int $gatewayId): void
    {
        DepositMethod::updateOrCreate(
            ['method_code' => 'bitnob_usdt'],
            [
                'payment_gateway_id'   => $gatewayId,
                'logo'                 => 'general/static/gateway/bitnob.png',
                'name'                 => 'Bitnob USDT',
                'type'                 => 'auto',
                'currency'             => 'USDT',
                'currency_symbol'      => '$',
                'min_deposit'          => 5.00,
                'max_deposit'          => 100000.00,
                'conversion_rate_live' => 1,
                'conversion_rate'      => 1.00,
                'charge_type'          => 'percent',
                'charge'               => 1.00,
                'user_charge'          => 1.00,
                'user_charge_type'     => 'percent',
                'merchant_charge'      => 0.50,
                'merchant_charge_type' => 'percent',
                'fields'               => json_encode([
                    'chain' => [
                        'type'     => 'select',
                        'label'    => 'Chain',
                        'options'  => ['tron' => 'TRON (TRC20)', 'bsc' => 'BSC (BEP20)', 'ethereum' => 'Ethereum (ERC20)'],
                        'required' => true,
                    ],
                ]),
                'receive_payment_details' => null,
                'status'                  => 1,
            ]
        );
    }

    protected function seedWithdrawMethod(int $gatewayId): void
    {
        WithdrawMethod::updateOrCreate(
            ['method_code' => 'bitnob_payout'],
            [
                'payment_gateway_id'   => $gatewayId,
                'logo'                 => 'general/static/gateway/bitnob.png',
                'name'                 => 'Bitnob Payout',
                'type'                 => 'auto',
                'currency'             => 'USD',
                'currency_symbol'      => '$',
                'min_withdraw'         => 10.00,
                'max_withdraw'         => 100000.00,
                'conversion_rate_live' => 1,
                'conversion_rate'      => 1.00,
                'charge_type'          => 'percent',
                'charge'               => 1.00,
                'user_charge'          => 1.00,
                'user_charge_type'     => 'percent',
                'merchant_charge'      => 0.50,
                'merchant_charge_type' => 'percent',
                'process_time_value'   => 30,
                'process_time_unit'    => 'minute',
                'fields'               => json_encode([
                    'destination_type' => [
                        'type'     => 'select',
                        'label'    => 'Destination',
                        'options'  => ['bank' => 'Bank Account', 'mobile_money' => 'Mobile Money'],
                        'required' => true,
                    ],
                    'country' => [
                        'type'     => 'text',
                        'label'    => 'Country (ISO-2 e.g. NG, KE, GH)',
                        'required' => true,
                    ],
                    'bank_code' => [
                        'type'     => 'text',
                        'label'    => 'Bank / mobile-money code',
                        'required' => true,
                    ],
                    'account_number' => [
                        'type'     => 'text',
                        'label'    => 'Account / phone number',
                        'required' => true,
                    ],
                    'account_name' => [
                        'type'     => 'text',
                        'label'    => 'Account holder name',
                        'required' => true,
                    ],
                ]),
                'status' => 1,
            ]
        );
    }
}
