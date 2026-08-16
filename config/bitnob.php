<?php

/*
|--------------------------------------------------------------------------
| Bitnob API
|--------------------------------------------------------------------------
| All Bitnob endpoints in one place. Credentials come from the
| `payment_gateways` row keyed by `bitnob` (BitnobSeeder seeds it).
|
| Auth: HMAC over CLIENT_ID:TIMESTAMP:NONCE:PAYLOAD using CLIENT_SECRET.
| Headers: X-Auth-Client, X-Auth-Timestamp, X-Auth-Nonce, X-Auth-Signature.
|
| Webhook signature is HMAC-SHA512 of the raw request body, sent in the
| header `x-bitnob-signature`. Bitnob retries 3x if the receiver doesn't
| return 200.
*/
return [

    'base_url' => [
        'sandbox' => env('BITNOB_SANDBOX_URL', 'https://api.bitnob.com'),
        'live'    => env('BITNOB_LIVE_URL', 'https://api.bitnob.com'),
    ],

    'timeout' => 20,

    /*
    | Endpoints — every path is overridable via .env so the provider
    | survives Bitnob API revisions without a code change. Defaults
    | match the most-recent published v1 API shape; if your Bitnob
    | sandbox / live account is on a different revision, drop the
    | correct path into .env and the provider picks it up after
    | `php artisan config:clear`.
    */
    'endpoints' => [
        // Auth probe
        'whoami' => env('BITNOB_PATH_WHOAMI', '/api/whoami'),

        // Card-user / KYC registry. Bitnob's virtual-card flow uses
        // a dedicated card-user resource separate from generic customers.
        'card_user_register' => env('BITNOB_PATH_CARD_USER_REGISTER', '/api/v1/virtualcards/registercardusersafe'),
        'card_user_get'      => env('BITNOB_PATH_CARD_USER_GET', '/api/v1/virtualcards/getcardcustomer'),

        // Generic customers (kept as a secondary lookup target).
        'customers'      => env('BITNOB_PATH_CUSTOMERS', '/api/v1/customers'),
        'customer'       => env('BITNOB_PATH_CUSTOMER', '/api/v1/customers/%s'),
        'customer_cards' => env('BITNOB_PATH_CUSTOMER_CARDS', '/api/v1/customers/%s/cards'),

        // Virtual cards
        'cards'             => env('BITNOB_PATH_CARD_CREATE', '/api/cards'),
        'card'              => env('BITNOB_PATH_CARD_GET', '/api/cards/%s'),
        'card_secure'       => env('BITNOB_PATH_CARD_SECURE', '/api/cards/%s/secure'),
        'card_balance'      => env('BITNOB_PATH_CARD_BALANCE', '/api/cards/%s/balance'),
        'card_withdraw'     => env('BITNOB_PATH_CARD_WITHDRAW', '/api/cards/%s/balance'),
        'card_status'       => env('BITNOB_PATH_CARD_STATUS', '/api/cards/%s/status'),
        'card_freeze'       => env('BITNOB_PATH_CARD_FREEZE', '/api/cards/%s/status'),
        'card_unfreeze'     => env('BITNOB_PATH_CARD_UNFREEZE', '/api/cards/%s/status'),
        'card_terminate'    => env('BITNOB_PATH_CARD_TERMINATE', '/api/cards/%s'),
        'card_limits'       => env('BITNOB_PATH_CARD_LIMITS', '/api/cards/%s/spend-limits'),
        'card_transactions' => env('BITNOB_PATH_CARD_TRANSACTIONS', '/api/cards/%s/transactions'),

        // Payouts (withdraw flow on the deposit side)
        'payout_quote'      => '/api/v1/payouts/quote',
        'payout_initialize' => '/api/v1/payouts/%s/initialize',
        'payout_finalize'   => '/api/v1/payouts/%s/finalize',
        'payout_lookup'     => '/api/v1/payouts/account-lookup',
        'payout_list'       => '/api/v1/payouts',
        'payout_show'       => '/api/v1/payouts/%s',
        'payout_countries'  => '/api/v1/payouts/supported-countries',
        'payout_country'    => '/api/v1/payouts/supported-countries/%s',
        'payout_banks'      => '/api/v1/payouts/banks/%s',
        'payout_limits'     => '/api/v1/payouts/limits',

        // Deposits / wallets — stablecoin deposit address.
        // The exact path may differ per Bitnob plan; override via env if needed.
        'deposit_address' => env('BITNOB_DEPOSIT_ADDRESS_ENDPOINT', '/api/v1/wallets/payments/initialize'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook event → handler method
    |--------------------------------------------------------------------------
    | The webhook controller dispatches incoming events through this map.
    | Adding a new event wired to a new handler is one row here.
    */
    'webhook_events' => [
        // Virtual cards
        'virtualcard.creation.success'     => 'handleCardCreated',
        'virtualcard.creation.failed'      => 'handleCardCreationFailed',
        'virtualcard.topup.success'        => 'handleCardTopupSuccess',
        'virtualcard.topup.failed'         => 'handleCardTopupFailed',
        'virtualcard.withdrawal.success'   => 'handleCardWithdrawalSuccess',
        'virtualcard.withdrawal.failed'    => 'handleCardWithdrawalFailed',
        'virtualcard.transaction.debit'    => 'handleCardDebit',
        'virtualcard.transaction.reversal' => 'handleCardReversal',
        'virtualcard.transaction.declined' => 'handleCardDeclined',
        'virtualcard.terminated'           => 'handleCardTerminated',

        // Stablecoin deposits
        'stablecoin.deposit.success' => 'handleStablecoinDepositSuccess',

        // Payouts
        'payout.success' => 'handlePayoutSuccess',
        'payout.failed'  => 'handlePayoutFailed',
    ],
];
