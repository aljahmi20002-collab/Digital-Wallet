<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $withdrawFields = [
            [
                'name'       => 'recipient_type',
                'type'       => 'select',
                'label'      => 'Recipient Type',
                'validation' => 'required',
                'options'    => [
                    'nuban'        => 'NUBAN (Nigeria bank)',
                    'ghipss'       => 'GHIPSS (Ghana bank)',
                    'mobile_money' => 'Mobile Money',
                    'basa'         => 'BASA (South Africa bank)',
                ],
            ],
            [
                'name'       => 'bank_code',
                'type'       => 'text',
                'label'      => 'Bank Code',
                'validation' => 'required',
            ],
            [
                'name'       => 'account_number',
                'type'       => 'text',
                'label'      => 'Account Number',
                'validation' => 'required',
            ],
            [
                'name'       => 'account_name',
                'type'       => 'text',
                'label'      => 'Account Holder Name',
                'validation' => 'required',
            ],
        ];

        $gateway = DB::table('payment_gateways')->where('code', 'paystack')->first();

        if ($gateway) {
            DB::table('payment_gateways')
                ->where('code', 'paystack')
                ->update([
                    'withdraw_field' => json_encode($withdrawFields),
                    'ipn'            => true,
                    'updated_at'     => now(),
                ]);
        } else {
            DB::table('payment_gateways')->insert([
                'logo'        => 'general/static/gateway/paystack.png',
                'name'        => 'Paystack',
                'code'        => 'paystack',
                'currencies'  => json_encode(['NGN', 'USD', 'GHS', 'ZAR', 'KES', 'XOF']),
                'credentials' => json_encode([
                    'public_key'     => 'public_key',
                    'secret_key'     => 'secret_key',
                    'merchant_email' => 'merchant@example.com',
                ]),
                'withdraw_field' => json_encode($withdrawFields),
                'ipn'            => true,
                'status'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_paystack');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_gateways')
            ->where('code', 'paystack')
            ->update([
                'withdraw_field' => null,
                'updated_at'     => now(),
            ]);

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_paystack');
    }
};
