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
        DB::table('payment_gateways')->updateOrInsert(
            ['code' => 'paymob'],
            [
                'logo'        => 'general/static/default/payment-gateway.png',
                'name'        => 'Paymob',
                'currencies'  => json_encode(['EGP', 'SAR', 'AED', 'OMR', 'USD']),
                'credentials' => json_encode([
                    'api_key'         => 'ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRFeU5ETXNJbTVoYldVaU9pSnBibWwwYVdGc0luMC5JNWY4aTI1U2ZqRDJUTTRkeURPal9GMi04X2J1WnlFckxGMFptTnBhOHJYbWlGWnl4OWxUeWMzNFRPQ3h0cXJyZGNyR1JQSHVnZjN3TkQwYW5mVDNkZw==',
                    'secret_key'      => 'sau_sk_test_b687bcf055f889cf872b8904644737593d3259fbc1249d743a052e22c6b177fd',
                    'public_key'      => 'sau_pk_test_ZmCnnWtMmISeebnY4r06rqQdtAEXoyx2',
                    'payment_methods' => '15182',
                    'hmac'            => 'AE0BDA8F01E4B67FE0CAC3C7DA74D9CA',
                    'base_url'        => 'https://ksa.paymob.com',
                    'sandbox'         => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_paymob');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_gateways')->where('code', 'paymob')->delete();

        Cache::forget('payment_gateways_all');
        Cache::forget('payment_gateway_code_paymob');
    }
};
