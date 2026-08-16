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
        $withdrawFields = $this->withdrawFields();
        $gateway        = DB::table('payment_gateways')->where('code', 'stripe')->first();

        if ($gateway) {
            DB::table('payment_gateways')
                ->where('code', 'stripe')
                ->update([
                    'withdraw_field' => json_encode($withdrawFields),
                    'ipn'            => true,
                    'updated_at'     => now(),
                ]);
        } else {
            DB::table('payment_gateways')->insert([
                'logo'        => 'general/static/gateway/stripe.png',
                'name'        => 'Stripe',
                'code'        => 'stripe',
                'currencies'  => json_encode(['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY']),
                'credentials' => json_encode([
                    'stripe_key'     => 'stripe_key',
                    'stripe_secret'  => 'stripe_secret',
                    'webhook_secret' => 'webhook_secret',
                    'sandbox'        => true,
                ]),
                'withdraw_field' => json_encode($withdrawFields),
                'ipn'            => true,
                'status'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_stripe');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_gateways')
            ->where('code', 'stripe')
            ->update([
                'withdraw_field' => null,
                'updated_at'     => now(),
            ]);

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_stripe');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function withdrawFields(): array
    {
        return [
            [
                'name'        => 'connected_account_id',
                'type'        => 'text',
                'label'       => 'Connected Account ID',
                'placeholder' => 'Optional: acct_...',
                'validation'  => 'nullable',
            ],
            [
                'name'        => 'destination',
                'type'        => 'text',
                'label'       => 'Destination ID',
                'placeholder' => 'Optional: ba_... or card_...',
                'validation'  => 'nullable',
            ],
            [
                'name'       => 'method',
                'type'       => 'select',
                'label'      => 'Payout Method',
                'validation' => 'required',
                'options'    => [
                    'standard' => 'Standard',
                    'instant'  => 'Instant',
                ],
            ],
            [
                'name'       => 'source_type',
                'type'       => 'select',
                'label'      => 'Source Balance',
                'validation' => 'nullable',
                'options'    => [
                    'card'         => 'Card',
                    'bank_account' => 'Bank Account',
                    'fpx'          => 'FPX',
                ],
            ],
            [
                'name'        => 'statement_descriptor',
                'type'        => 'text',
                'label'       => 'Statement Descriptor',
                'placeholder' => 'Optional, max 22 characters',
                'validation'  => 'nullable',
            ],
        ];
    }
};
