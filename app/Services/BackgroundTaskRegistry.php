<?php

namespace App\Services;

class BackgroundTaskRegistry
{
    /** @var array<string, array<string, mixed>> */
    private static array $commands = [
        'wallet_earn_process' => [
            'key'         => 'wallet_earn_process',
            'label'       => 'Wallet Earn Reward Processing',
            'signature'   => 'wallet-earn:process',
            'description' => 'Process due Wallet Earn reward payouts and matured principal returns.',
            'options'     => [
                'limit' => [
                    'type'    => 'integer',
                    'default' => 100,
                    'min'     => 1,
                    'max'     => 1000,
                    'label'   => 'Limit',
                    'help'    => 'Maximum number of stakes to process in one run (1-1000).',
                ],
            ],
        ],
        'subscription_process' => [
            'key'         => 'subscription_process',
            'label'       => 'Subscription Lifecycle Processing',
            'signature'   => 'subscription:process',
            'description' => 'Expire overdue subscriptions, convert completed trials, and optionally process auto-renewals.',
            'options'     => [
                'renewals' => [
                    'type'    => 'boolean',
                    'default' => true,
                    'label'   => 'Process auto-renewals',
                    'help'    => 'Attempt due wallet auto-renewals before expiry and grace checks.',
                ],
            ],
        ],
        'p2p_promotions_expire' => [
            'key'         => 'p2p_promotions_expire',
            'label'       => 'P2P Offer Promotions Expiry',
            'signature'   => 'p2p:promotions:expire',
            'description' => 'Expire active P2P offer promotions that passed their end time.',
            'options'     => [],
        ],
        'p2p_orders_expire' => [
            'key'         => 'p2p_orders_expire',
            'label'       => 'P2P Order Expiry',
            'signature'   => 'p2p:orders:expire',
            'description' => 'Expire pending P2P orders that passed expiry time and refund escrow if applicable.',
            'options'     => [],
        ],
    ];

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return self::$commands;
    }

    /** @return array<string, mixed>|null */
    public function get(string $key): ?array
    {
        return self::$commands[$key] ?? null;
    }

    public function exists(string $key): bool
    {
        return isset(self::$commands[$key]);
    }

    public function count(): int
    {
        return count(self::$commands);
    }
}
