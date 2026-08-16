<?php

/*
|--------------------------------------------------------------------------
| Feature Catalog
|--------------------------------------------------------------------------
|
| Central catalog of every manageable platform feature. This file drives
| the FeatureSeeder and acts as the source of truth when syncing to the
| `features` and `feature_access_rules` tables. Add new features here –
| no further code changes are required for the admin UI to pick them up.
|
| Panels: 'user', 'merchant', 'agent'
| (The 'admin' panel is always considered accessible for admins so that
| the feature itself can be managed from the admin side.)
|
| "is_core" features (like deposit/withdraw) can be toggled but the UI
| warns the admin first, since disabling them typically breaks business
| flows that depend on them.
|
| "manage_mode" may be set to "role_toggle" for features that only
| enable or disable a user role surface. Those features are shown in a
| protected role-controls section and do not expose panel rule editing.
|
| The "rules" key defines default business constraints. It is merged
| into feature_access_rules.conditions on seed and can be changed from
| the admin UI at runtime (requires_kyc, requires_phone, countries_allowed).
|
*/

return [

    'categories' => [
        'money_movement' => [
            'label' => 'Money Movement',
            'icon'  => 'wallet',
            'order' => 10,
        ],
        'p2p' => [
            'label' => 'P2P Marketplace',
            'icon'  => 'p2p_trading',
            'order' => 20,
        ],
        'business' => [
            'label' => 'Business & Merchant',
            'icon'  => 'merchant',
            'order' => 30,
        ],
        'cards' => [
            'label' => 'Virtual Cards',
            'icon'  => 'card',
            'order' => 40,
        ],
        'engagement' => [
            'label' => 'Engagement & Growth',
            'icon'  => 'referral',
            'order' => 50,
        ],
    ],

    'features' => [

        'deposit_money' => [
            'label'       => 'Deposit Money',
            'description' => 'Allow users to top up their wallet using any active payment gateway or manual deposit method.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'is_core'     => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'withdraw_money' => [
            'label'       => 'Withdraw Money',
            'description' => 'Allow users to transfer wallet balance to linked bank, mobile money, or crypto payout methods.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'is_core'     => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'send_money' => [
            'label'       => 'Send Money',
            'description' => 'Allow users to transfer wallet balance instantly to another platform user.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'request_money' => [
            'label'       => 'Request Money',
            'description' => 'Allow users to raise payment requests that another user can approve and pay from their wallet.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'exchange_money' => [
            'label'       => 'Exchange Money',
            'description' => 'Let users convert balance between supported wallet currencies using the exchange rate engine.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'wallet_earn' => [
            'label'       => 'Wallet Earn',
            'description' => 'Allow users to stake wallet balances in supported currencies and earn scheduled rewards.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'mobile_recharge' => [
            'label'       => 'Mobile Recharge',
            'description' => 'Allow users, merchants and agents to recharge mobile numbers from wallet balance through the configured provider.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc'   => false,
                'requires_phone' => true,
            ],
        ],

        'bank_transfer' => [
            'label'       => 'Bank Transfer Payouts',
            'description' => 'Expose linked-bank withdraw methods to end users. Disabling hides bank payout options everywhere.',
            'category'    => 'money_movement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'payment_link' => [
            'label'       => 'Payment Links',
            'description' => 'Allow users, merchants and agents to generate shareable payment links that anyone can pay from a wallet or supported gateway.',
            'category'    => 'business',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'merchant_payment' => [
            'label'       => 'Merchant Payment',
            'description' => 'Allow customers to pay registered merchants from their wallet or connected payment methods.',
            'category'    => 'business',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'agent_program' => [
            'label'       => 'Agent Program',
            'description' => 'Enable agent registration, agent login, and the agent dashboard. When disabled, every agent surface is hidden across the platform.',
            'category'    => 'business',
            'manage_mode' => 'role_toggle',
            'is_enabled'  => true,
            'is_core'     => false,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'subscription_system' => [
            'label'       => 'Subscription System',
            'description' => 'Enable subscription plans, checkout, active plan status, renewals, cancellations, and subscription history for users.',
            'category'    => 'business',
            'is_enabled'  => true,
            'is_core'     => false,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'p2p_marketplace' => [
            'label'       => 'P2P Marketplace',
            'description' => 'Peer-to-peer ads, trading rooms, payment methods, and disputes. Disabling hides every P2P surface.',
            'category'    => 'p2p',
            'is_enabled'  => true,
            'is_core'     => false,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc'      => true,
                'countries_allowed' => [],
            ],
        ],

        'virtual_card' => [
            'label'       => 'Virtual Cards',
            'description' => 'Issue, manage, and transact on virtual cards backed by the configured card providers.',
            'category'    => 'cards',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => true,
            ],
        ],

        'referral_program' => [
            'label'       => 'Referral Program',
            'description' => 'Referral link, referral tree, and referral reward settings exposed to the end user.',
            'category'    => 'engagement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [],
        ],

        'user_ranks' => [
            'label'       => 'User Ranks',
            'description' => 'Manage rank progression, wallet limits, referral levels, and reward tiers from Feature Management.',
            'category'    => 'engagement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [],
        ],

        'vouchers' => [
            'label'       => 'Vouchers',
            'description' => 'Redeemable gift/top-up vouchers for wallet balance. Controls both creation and redemption surfaces.',
            'category'    => 'engagement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

        'gift_cards' => [
            'label'       => 'Gift Cards',
            'description' => 'Designed gift cards funded from a wallet, delivered by email with a private redeem link and a public preview page.',
            'category'    => 'engagement',
            'is_enabled'  => true,
            'panels'      => ['user' => true, 'merchant' => true, 'agent' => true],
            'rules'       => [
                'requires_kyc' => false,
            ],
        ],

    ],

];
