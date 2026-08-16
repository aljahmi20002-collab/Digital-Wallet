<?php

use App\Services\VirtualCard\Drivers\Bitnob\BitnobCardProvider;
use App\Services\VirtualCard\Drivers\Stripe\StripeCardProvider;
use App\Services\VirtualCard\Drivers\StroWallet\StroWalletProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Provider Implementations
    |--------------------------------------------------------------------------
    | Map provider `code` (column on virtual_card_providers) to the class that
    | implements VirtualCardProviderInterface. Adding a new gateway is a single
    | line here — no factory edits, no UI edits.
    */
    'providers' => [
        'stripe'     => StripeCardProvider::class,
        'strowallet' => StroWalletProvider::class,
        'bitnob'     => BitnobCardProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Capabilities
    |--------------------------------------------------------------------------
    | Used when a provider row is missing the `capabilities` JSON. Every
    | capability defaults to false except issuance, so the UI hides actions
    | that the provider has not opted in to.
    */
    'default_capabilities' => [
        'issue'        => true,
        'card_details' => true,
        'topup'        => false,
        'withdraw'     => false,
        'freeze'       => false,
        'limits'       => false,
        'controls'     => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-provider Capability Overrides
    |--------------------------------------------------------------------------
    | The DB `capabilities` column wins over this. This map seeds and acts as
    | the fallback when a provider row is missing capabilities data.
    */
    'capabilities' => [
        'stripe' => [
            'issue'        => true,
            'card_details' => true,
            // Stripe Issuing has no native "fund this card" endpoint —
            // Treasury (enterprise-only) is the first-class flow. We
            // model top-up / withdraw via the card's
            // `spending_controls.spending_limits` (all_time interval):
            // top-up raises the ceiling, withdraw lowers it. The wallet
            // ledger on our side handles the rest.
            'topup'    => true,
            'withdraw' => true,
            'freeze'   => true,
            'limits'   => true,
            'controls' => true,
        ],
        'strowallet' => [
            'issue'        => true,
            'card_details' => true,
            'topup'        => true,
            'withdraw'     => true,
            'freeze'       => false, // no API; UI hides the freeze action
            'limits'       => false,
            'controls'     => false,
        ],
        'bitnob' => [
            'issue'        => true,
            'card_details' => true,
            'topup'        => true,
            'withdraw'     => true,
            'freeze'       => true,
            'limits'       => true,
            'controls'     => false, // Bitnob has no per-card "online/atm/intl/contactless" toggle endpoint
        ],
    ],
];
