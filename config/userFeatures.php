<?php

return [
    'features' => [
        [
            'feature'     => 'account_status',
            'icon'        => 'fa-user-shield',
            'description' => 'Controls user login access.',
            'status'      => true,
        ],
        [
            'feature'     => 'email_verification',
            'icon'        => 'fa-envelope-open-text',
            'description' => 'Requires email verification to activate the account.',
            'status'      => false,
        ],
        [
            'feature'     => 'kyc_verification',
            'icon'        => 'fa-id-card',
            'description' => 'Requires KYC verification before transactions.',
            'status'      => false,
        ],
        [
            'feature'     => 'deposit',
            'icon'        => 'fa-circle-arrow-down',
            'description' => 'Allows users to add funds to their wallet.',
            'status'      => true,
        ],
        [
            'feature'     => 'exchange_money',
            'icon'        => 'fa-right-left',
            'description' => 'Allows currency conversion within the wallet.',
            'status'      => true,
        ],
        [
            'feature'     => 'send_money',
            'icon'        => 'fa-paper-plane',
            'description' => 'Allows sending money to other users.',
            'status'      => true,
        ],
        [
            'feature'     => 'request_money',
            'icon'        => 'fa-hand-holding-dollar',
            'description' => 'Allows users to request money from others.',
            'status'      => true,
        ],
        [
            'feature'     => 'mobile_recharge',
            'icon'        => 'fa-mobile-screen-button',
            'description' => 'Allows users to recharge mobile numbers from wallet balance.',
            'status'      => true,
        ],
        [
            'feature'     => 'withdraw',
            'icon'        => 'fa-circle-arrow-up',
            'description' => 'Allows withdrawal to linked bank accounts.',
            'status'      => true,
        ],
        [
            'feature'     => 'gift_cards',
            'icon'        => 'fa-gift',
            'description' => 'Allows users to send designed gift cards funded from a wallet.',
            'status'      => true,
        ],
    ],
];
