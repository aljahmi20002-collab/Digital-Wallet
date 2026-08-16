<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationActionType;
use App\Enums\UserType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class SubscriptionNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'identifier'  => 'subscription_user_trial_started',
                'name'        => 'Subscription Trial Started',
                'icon'        => 'notification',
                'action_type' => NotificationActionType::CREATED,
                'info'        => 'User is notified when a paid subscription trial starts without an immediate charge.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cycle', 'trial_ends_at', 'auto_renew'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Your {plan} Trial Has Started',
                        'message'   => 'Your free trial for {plan} has started. Billing cycle: {cycle}. Trial ends at {trial_ends_at}. Auto-renew: {auto_renew}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => '{plan} Trial Started',
                        'message'   => 'Your trial runs until {trial_ends_at}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} trial started. Ends: {trial_ends_at}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_started',
                'name'        => 'Subscription Started',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::CREATED,
                'info'        => 'User is notified when a subscription becomes active after checkout.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cycle', 'amount', 'period_end', 'trx', 'auto_renew'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Your {plan} Subscription is Active',
                        'message'   => 'Your {plan} subscription is active. Billing cycle: {cycle}. Amount charged: {amount}. Access runs through {period_end}. Transaction: {trx}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Subscription Active',
                        'message'   => '{plan} is active until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} active. Charged {amount}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_plan_switched',
                'name'        => 'Subscription Plan Switched',
                'icon'        => 'layer',
                'action_type' => NotificationActionType::CREATED,
                'info'        => 'User is notified when they switch from one subscription plan to another.',
                'user_type'   => UserType::USER,
                'variables'   => ['previous_plan', 'plan', 'cycle', 'charge', 'credit', 'period_end', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Subscription Switched to {plan}',
                        'message'   => 'Your subscription was switched from {previous_plan} to {plan}. Billing cycle: {cycle}. Prorated credit: {credit}. Charged: {charge}. Access runs through {period_end}. Transaction: {trx}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Plan Switched',
                        'message'   => 'You switched from {previous_plan} to {plan}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Subscription switched to {plan}. Charged {charge}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_renewed',
                'name'        => 'Subscription Renewed',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'User is notified when a subscription renewal succeeds.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cycle', 'amount', 'period_end', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Subscription Renewed',
                        'message'   => 'Your {plan} subscription has been renewed for {cycle}. Amount charged: {amount}. New access end: {period_end}. Transaction: {trx}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Subscription Renewed',
                        'message'   => '{plan} renewed until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} renewed. Charged {amount}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_trial_converted',
                'name'        => 'Subscription Trial Converted',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'User is notified when a trial converts to a paid active subscription.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cycle', 'amount', 'period_end', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Trial Converted',
                        'message'   => 'Your {plan} trial converted to an active subscription. Billing cycle: {cycle}. Amount charged: {amount}. Access runs through {period_end}. Transaction: {trx}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Trial Converted',
                        'message'   => '{plan} is now active until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} trial converted. Charged {amount}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_grace_started',
                'name'        => 'Subscription Grace Period Started',
                'icon'        => 'warning-2',
                'action_type' => NotificationActionType::FAILED,
                'info'        => 'User is notified when a renewal or trial conversion fails and grace access starts.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'period_end', 'grace_ends_at', 'auto_renew'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Renewal Needs Attention',
                        'message'   => 'We could not complete renewal for {plan}. Your grace period runs until {grace_ends_at}. Please add funds or renew before access expires.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Grace Period Started',
                        'message'   => '{plan} grace access ends at {grace_ends_at}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} renewal failed. Grace ends {grace_ends_at}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_expired',
                'name'        => 'Subscription Expired',
                'icon'        => 'warning-2',
                'action_type' => NotificationActionType::FAILED,
                'info'        => 'User is notified when subscription access expires.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'status', 'period_end', 'grace_ends_at'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Subscription Expired',
                        'message'   => 'Your {plan} subscription has expired. Status: {status}. Renew your subscription to restore premium access.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Subscription Expired',
                        'message'   => '{plan} access has expired.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} subscription expired.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_cancelled',
                'name'        => 'Subscription Cancelled',
                'icon'        => 'close',
                'action_type' => NotificationActionType::REJECTED,
                'info'        => 'User is notified when subscription cancellation is confirmed.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cancelled_by', 'period_end', 'status'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Subscription Cancelled',
                        'message'   => 'Your {plan} subscription cancellation is confirmed by {cancelled_by}. Current status: {status}. Access is available until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Subscription Cancelled',
                        'message'   => '{plan} cancellation confirmed. Access until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} cancelled. Access until {period_end}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'subscription_user_admin_activated',
                'name'        => 'Subscription Activated by Admin',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::APPROVED,
                'info'        => 'User is notified when an admin manually activates a subscription.',
                'user_type'   => UserType::USER,
                'variables'   => ['plan', 'cycle', 'period_end'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => '{plan} Subscription Activated',
                        'message'   => 'Your {plan} subscription was activated by the admin team. Billing cycle: {cycle}. Access runs through {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Subscription Activated',
                        'message'   => '{plan} is active until {period_end}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => '{plan} activated until {period_end}.',
                        'is_active' => false,
                    ],
                ],
            ],
        ];

        foreach ($templates as $data) {
            $template = NotificationTemplate::query()->updateOrCreate(
                ['identifier' => $data['identifier']],
                collect($data)->except('channels')->toArray()
            );

            $template->channels()->delete();
            $template->channels()->createMany($data['channels']);
        }
    }
}
