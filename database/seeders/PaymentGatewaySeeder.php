<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Seed the payment_gateways table with unique gateways.
     */
    public function run(): void
    {
        $gateways = [
            [
                'code'       => 'moneroo',
                'logo'       => 'general/static/gateway/moneroo.svg',
                'name'       => 'Moneroo',
                'currencies' => json_encode([
                    // Officially supported payment currencies per Moneroo docs
                    // https://docs.moneroo.io/payments/available-methods (see currency column)
                    'USD', 'EUR', 'NGN', 'GHS', 'KES', 'TZS', 'UGX', 'XAF', 'XOF', 'ZAR', 'ZMW', 'RWF', 'CDF', 'GNF', 'MWK',
                ]),
                'credentials' => json_encode([
                    'api_key'    => 'pvk_sandbox_teb330|01K120C7BN2TXPT6D1BYQFEZ24',
                    'api_secret' => 'digikash',
                    'sandbox'    => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => 1,
            ],
            [
                'code'        => 'paymob',
                'logo'        => 'general/static/default/payment-gateway.png',
                'name'        => 'Paymob',
                'currencies'  => json_encode(['EGP', 'SAR', 'AED', 'OMR', 'USD']),
                'credentials' => json_encode([
                    'api_key'         => 'api_key',
                    'secret_key'      => 'secret_key',
                    'public_key'      => 'public_key',
                    'payment_methods' => 'integration_id',
                    'hmac'            => 'hmac_secret',
                    'base_url'        => 'https://ksa.paymob.com',
                    'sandbox'         => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'stripe',
                'logo'        => 'general/static/gateway/stripe.png',
                'name'        => 'Stripe',
                'currencies'  => json_encode(['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY']),
                'credentials' => json_encode([
                    'stripe_key'     => 'stripe_key',
                    'stripe_secret'  => 'stripe_secret',
                    'webhook_secret' => 'webhook_secret',
                    'sandbox'        => true,
                ]),
                'withdraw_field' => json_encode([
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
                ]),
                'ipn'    => true,
                'status' => true,
            ],
            [
                'code'        => 'strowallet',
                'logo'        => 'general/static/gateway/strowallet.png',
                'name'        => 'Strowallet',
                'currencies'  => json_encode(['USD', 'NGN']),
                'credentials' => json_encode([
                    'public_key' => 'public_key',
                    'secret_key' => 'secret_key',
                    'sandbox'    => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => false,
                'status'         => true,
            ],
            [
                'code'        => 'binance',
                'logo'        => 'general/static/gateway/binance.png',
                'name'        => 'Binance Pay',
                'currencies'  => json_encode(['USDT', 'BTC', 'ETH', 'BNB', 'BUSD', 'USD', 'EUR']),
                'credentials' => json_encode([
                    'certificate_sn' => 'certificate_sn',
                    'private_key'    => 'private_key',
                    'sandbox'        => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'airtel',
                'logo'        => 'general/static/gateway/airtel.png',
                'name'        => 'Airtel Money',
                'currencies'  => json_encode(['UGX', 'KES', 'TZS', 'RWF', 'ZMW']),
                'credentials' => json_encode([
                    'client_id'     => 'client_id',
                    'client_secret' => 'client_secret',
                    'country'       => 'UG',
                    'currency'      => 'UGX',
                    'sandbox'       => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'blockchain',
                'logo'        => 'general/static/gateway/blockchain.png',
                'name'        => 'Blockchain.info',
                'currencies'  => json_encode(['BTC']),
                'credentials' => json_encode([
                    'receive_address'        => 'receive_address',
                    'callback_secret'        => 'callback_secret',
                    'required_confirmations' => 1,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'blockio',
                'logo'        => 'general/static/gateway/blockio.png',
                'name'        => 'Block.io',
                'currencies'  => json_encode(['BTC', 'LTC', 'DOGE']),
                'credentials' => json_encode([
                    'api_key'                => 'api_key',
                    'required_confirmations' => 1,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'bitpayserver',
                'logo'        => 'general/static/gateway/btcpayserver.png',
                'name'        => 'BTCPay Server',
                'currencies'  => json_encode(['BTC', 'USD', 'EUR', 'GBP']),
                'credentials' => json_encode([
                    'server_url' => 'https://your-btcpay-server.com',
                    'api_token'  => 'api_token',
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'cashmaal',
                'logo'        => 'general/static/gateway/cashmaal.png',
                'name'        => 'Cashmaal',
                'currencies'  => json_encode(['USD', 'EUR', 'GBP', 'PKR']),
                'credentials' => json_encode([
                    'web_id' => 'web_id',
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'coingate',
                'logo'        => 'general/static/gateway/coingate.png',
                'name'        => 'CoinGate',
                'currencies'  => json_encode(['EUR', 'USD', 'BTC', 'ETH', 'LTC']),
                'credentials' => json_encode([
                    'auth_token'       => 'auth_token',
                    'receive_currency' => 'EUR',
                    'sandbox'          => false,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'coinpayments',
                'logo'        => 'general/static/gateway/coinpayments.svg',
                'name'        => 'CoinPayments',
                'currencies'  => json_encode(['BTC', 'ETH', 'LTC', 'USDT', 'USD', 'EUR']),
                'credentials' => json_encode([
                    'public_key'  => 'public_key',
                    'private_key' => 'private_key',
                    'ipn_secret'  => 'ipn_secret',
                    'currency2'   => 'BTC',
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'instamojo',
                'logo'        => 'general/static/gateway/instamojo.png',
                'name'        => 'Instamojo',
                'currencies'  => json_encode(['INR']),
                'credentials' => json_encode([
                    'api_key'    => 'api_key',
                    'auth_token' => 'auth_token',
                    'phone'      => '9999999999',
                    'sandbox'    => false,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'mtn',
                'logo'        => 'general/static/gateway/mtn.png',
                'name'        => 'MTN Mobile Money',
                'currencies'  => json_encode(['UGX', 'GHS', 'ZAR', 'XAF', 'EUR']),
                'credentials' => json_encode([
                    'subscription_key' => 'subscription_key',
                    'user_id'          => 'user_id',
                    'api_key'          => 'api_key',
                    'test_msisdn'      => '256774290781',
                    'sandbox'          => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'nowpayments',
                'logo'        => 'general/static/gateway/nowpayments.png',
                'name'        => 'NOWPayments',
                'currencies'  => json_encode(['BTC', 'ETH', 'USDT', 'LTC', 'BCH', 'USD', 'EUR']),
                'credentials' => json_encode([
                    'api_key'      => 'api_key',
                    'ipn_secret'   => 'ipn_secret',
                    'pay_currency' => 'BTC',
                    'sandbox'      => false,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'razorpay',
                'logo'        => 'general/static/gateway/razorpay.png',
                'name'        => 'Razorpay',
                'currencies'  => json_encode(['INR', 'USD', 'EUR', 'GBP']),
                'credentials' => json_encode([
                    'key_id'     => 'key_id',
                    'key_secret' => 'key_secret',
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'twocheckout',
                'logo'        => 'general/static/gateway/twocheckout.png',
                'name'        => '2Checkout',
                'currencies'  => json_encode(['USD', 'EUR', 'GBP', 'CAD', 'AUD']),
                'credentials' => json_encode([
                    'merchant_code' => '...',
                    'secret_key'    => '...',
                    'sandbox'       => true,
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'voguepay',
                'logo'        => 'general/static/gateway/voguepay.png',
                'name'        => 'Voguepay',
                'currencies'  => json_encode(['NGN', 'USD', 'GBP', 'EUR']),
                'credentials' => json_encode([
                    'merchant_id' => 'merchant_id',
                ]),
                'withdraw_field' => null,
                'ipn'            => true,
                'status'         => true,
            ],
            [
                'code'        => 'bitnob',
                'logo'        => 'general/static/gateway/bitnob.png',
                'name'        => 'Bitnob',
                'currencies'  => json_encode(['USD', 'NGN', 'KES', 'GHS', 'USDT']),
                'credentials' => json_encode([
                    'client_id'      => '7e09cd3e-7a76-412d-9df0-12aebacac6c4',
                    'hmac_key'       => 'hsk.087026e93e634c859b971f71fd9a210c.4967d36af6f9424ba0da0a256ea0b1a1cbed4f2498ca429988ab24f06f78eac1',
                    'public_key'     => 'pk.df4be8d0a6214f2ca324202b326898ce.3bb91bf790c443cf895d9b4a59e53e65761573a92c0f4a8dbecdf3cc89990b6b',
                    'secret_key'     => 'sk.f5b2a26f0c5c488bbfd4df1dd2b2f762.ccc20f2f19c94caa9b63137315f706cbb8390b1e9c974c8ca4c10daaf235a387',
                    'lightning_key'  => 'ln.07943973ebed4eb2b72a036fc7359105.c6cf505aefc84d649edf43cf4d95135f623de5eaee5b4e00884e71938f209d21',
                    'webhook_secret' => 'c4d7fea73754bb68bde7',
                    'sandbox'        => true,
                    'signature_algo' => 'sha256',
                ]),
                'withdraw_field' => json_encode([
                    'destination_type' => [
                        'type'     => 'select',
                        'label'    => 'Destination',
                        'options'  => ['bank' => 'Bank Account', 'mobile_money' => 'Mobile Money'],
                        'required' => true,
                    ],
                    'country' => [
                        'type'     => 'text',
                        'label'    => 'Country (ISO-2)',
                        'required' => true,
                    ],
                    'bank_code' => [
                        'type'     => 'text',
                        'label'    => 'Bank / Mobile-money code',
                        'required' => true,
                    ],
                    'account_number' => [
                        'type'     => 'text',
                        'label'    => 'Account number',
                        'required' => true,
                    ],
                    'account_name' => [
                        'type'     => 'text',
                        'label'    => 'Account holder name',
                        'required' => true,
                    ],
                ]),
                'ipn'    => true,
                'status' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            $attributes = ['code' => $gateway['code']];
            $values     = $gateway;
            unset($values['code']);
            DB::table('payment_gateways')->updateOrInsert($attributes, $values);
        }
    }
}
