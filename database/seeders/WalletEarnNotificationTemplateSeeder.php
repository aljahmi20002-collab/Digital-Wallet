<?php

namespace Database\Seeders;

use App\Enums\NotificationActionType;
use App\Enums\UserType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class WalletEarnNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'identifier'  => 'wallet_earn_admin_stake_pending',
                'name'        => 'Wallet Earn Stake Pending Review',
                'icon'        => 'trending-up',
                'action_type' => NotificationActionType::REQUESTED,
                'info'        => 'Admin is alerted when a Wallet Earn stake is waiting for manual review.',
                'user_type'   => UserType::ADMIN,
                'variables'   => ['user', 'plan', 'amount', 'expected_profit', 'status', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'New Wallet Earn Stake Pending Review',
                        'message'   => '{user} created a {amount} Wallet Earn stake in {plan}. Expected profit: {expected_profit}. Transaction: {trx}. Please review it.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Review Needed',
                        'message'   => '{user} staked {amount} in {plan}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn review: {user}, {amount}, {plan}, trx {trx}',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_stake_created',
                'name'        => 'Wallet Earn Stake Created',
                'icon'        => 'trending-up',
                'action_type' => NotificationActionType::CREATED,
                'info'        => 'User is notified after creating a Wallet Earn stake.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'amount', 'expected_profit', 'status', 'next_payout_at', 'maturity_date', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Stake Created',
                        'message'   => 'Your {amount} Wallet Earn stake in {plan} was created with {status} status. Expected profit: {expected_profit}. Next payout: {next_payout_at}. Maturity: {maturity_date}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Stake Created',
                        'message'   => 'Your {amount} stake in {plan} is {status}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn stake {amount} in {plan} is {status}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_stake_approved',
                'name'        => 'Wallet Earn Stake Approved',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::APPROVED,
                'info'        => 'User is notified when a pending Wallet Earn stake is approved.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'amount', 'expected_profit', 'next_payout_at', 'maturity_date', 'review_note'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Stake Approved',
                        'message'   => 'Your {amount} stake in {plan} is approved and active. Expected profit: {expected_profit}. First payout: {next_payout_at}. Maturity: {maturity_date}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Approved',
                        'message'   => '{plan} stake is active. First payout: {next_payout_at}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn approved: {amount} in {plan}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_stake_rejected',
                'name'        => 'Wallet Earn Stake Rejected',
                'icon'        => 'card-request',
                'action_type' => NotificationActionType::REJECTED,
                'info'        => 'User is notified when a pending Wallet Earn stake is rejected and principal is returned.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'amount', 'review_note', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Stake Rejected',
                        'message'   => 'Your {amount} stake in {plan} was rejected and the principal was returned. Reason: {review_note}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Rejected',
                        'message'   => '{plan} stake was rejected. Principal returned.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn rejected: {amount} in {plan}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_stake_canceled',
                'name'        => 'Wallet Earn Stake Canceled',
                'icon'        => 'card-request',
                'action_type' => NotificationActionType::REJECTED,
                'info'        => 'User is notified when a Wallet Earn stake is canceled and principal is returned.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'amount', 'review_note', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Stake Canceled',
                        'message'   => 'Your {amount} stake in {plan} was canceled and the principal was returned. Note: {review_note}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Canceled',
                        'message'   => '{plan} stake was canceled. Principal returned.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn canceled: {amount} in {plan}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_reward_paid',
                'name'        => 'Wallet Earn Reward Paid',
                'icon'        => 'money-plus',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'User is notified when a Wallet Earn reward payout is credited.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'profit', 'payout_number', 'paid_profit', 'next_payout_at'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Reward Paid',
                        'message'   => 'Reward payout #{payout_number} from {plan} has been paid: {profit}. Total profit paid so far: {paid_profit}. Next payout: {next_payout_at}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Reward Paid',
                        'message'   => 'Payout #{payout_number} paid: {profit}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn payout #{payout_number}: {profit}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'wallet_earn_user_stake_completed',
                'name'        => 'Wallet Earn Stake Completed',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'User is notified when a Wallet Earn stake reaches completion.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'amount', 'paid_profit', 'principal_returned', 'maturity_date'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Wallet Earn Stake Completed',
                        'message'   => 'Your {amount} stake in {plan} is complete. Total profit paid: {paid_profit}. Principal returned: {principal_returned}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Wallet Earn Completed',
                        'message'   => '{plan} completed. Profit paid: {paid_profit}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Wallet Earn completed: {plan}, profit {paid_profit}.',
                        'is_active' => false,
                    ],
                ],
            ],
        ];

        foreach ($templates as $data) {
            $template = NotificationTemplate::updateOrCreate(
                ['identifier' => $data['identifier']],
                collect($data)->except('channels')->toArray()
            );

            $template->channels()->delete();
            $template->channels()->createMany($data['channels']);
        }
    }
}
