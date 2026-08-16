<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationActionType;
use App\Enums\UserType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class AgentNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'identifier'  => 'agent_admin_notify_request',
                'name'        => 'Agent Request Submission',
                'icon'        => 'card-request',
                'action_type' => NotificationActionType::REQUESTED,
                'info'        => 'Admin is alerted when a user submits a new agent registration request.',
                'user_type'   => UserType::ADMIN,
                'variables'   => ['user', 'agent_name', 'agent_code', 'currencies', 'operating_note', 'email', 'phone'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'New Agent Account Request',
                        'message'   => 'User {user} submitted a new agent account request for "{agent_name}" (code: {agent_code}). Supported currencies: {currencies}. Operating note: {operating_note}. Please review and approve.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Agent Account Request',
                        'message'   => '{user} requested an agent account for {agent_name} ({currencies}).',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Agent request: {user}, {agent_name}, {currencies}',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_user_notify_request_approved',
                'name'        => 'Agent Request Approved',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::APPROVED,
                'info'        => 'User is notified when their agent registration request is approved.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'currencies'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Your Agent Account is Approved',
                        'message'   => 'Congratulations! Your agent account "{agent_name}" has been approved and is now live for these currencies: {currencies}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Agent Approved',
                        'message'   => 'Your agent account "{agent_name}" is approved for {currencies}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Agent approved: {agent_name} ({currencies})',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_user_notify_request_rejected',
                'name'        => 'Agent Request Rejected',
                'icon'        => 'card-request',
                'action_type' => NotificationActionType::REJECTED,
                'info'        => 'User is notified when their agent registration request is rejected.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'currencies', 'rejection_reason'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Agent Request Rejected',
                        'message'   => 'Your agent account request for "{agent_name}" ({currencies}) was rejected. Reason: {rejection_reason}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Agent Rejected',
                        'message'   => 'Your agent request "{agent_name}" was rejected.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Agent rejected: {agent_name} ({currencies}) - {rejection_reason}',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_qr_cash_out_requested',
                'name'        => 'Agent QR Cash-Out Request',
                'icon'        => 'qrcode',
                'action_type' => NotificationActionType::REQUESTED,
                'info'        => 'Agent is notified when a customer confirms cash-out by scanning the static agent QR.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'customer', 'amount', 'reference', 'currency', 'cash_out_link'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'QR Cash-Out Waiting for Cash Handover',
                        'message'   => '{customer} confirmed {amount} cash-out at {agent_name}. Reference: {reference}. Pay cash only after matching this reference.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'QR Cash-Out Waiting',
                        'message'   => '{customer} confirmed {amount}. Match reference {reference} before paying cash.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'QR cash-out {amount}, ref {reference}. Pay cash after matching.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_assisted_cash_out_otp',
                'name'        => 'Agent Assisted Cash-Out OTP',
                'icon'        => 'password',
                'action_type' => NotificationActionType::REQUESTED,
                'info'        => 'Customer receives an OTP before an agent can complete assisted cash-out from the counter.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'customer', 'amount', 'otp', 'expires_minutes'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Cash-Out OTP',
                        'message'   => 'Your cash-out OTP for {amount} at {agent_name} is {otp}. It expires in {expires_minutes} minutes. Share it only with the counter agent when you are present.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Cash-Out OTP',
                        'message'   => 'OTP {otp} for {amount} at {agent_name}. Expires in {expires_minutes} minutes.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Cash-out OTP {otp} for {amount} at {agent_name}. Expires in {expires_minutes} min.',
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_qr_cash_out_customer_confirmed',
                'name'        => 'Customer QR Cash-Out Confirmed',
                'icon'        => 'send-money',
                'action_type' => NotificationActionType::REQUESTED,
                'info'        => 'Customer is notified after confirming wallet debit from an agent QR cash-out.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'amount', 'reference'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Cash-Out Confirmed',
                        'message'   => 'You confirmed {amount} cash-out at {agent_name}. Reference: {reference}. Collect cash from the agent after they match the reference.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Cash-Out Confirmed',
                        'message'   => '{amount} cash-out confirmed. Reference: {reference}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Cash-out confirmed {amount}. Ref {reference}.',
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'identifier'  => 'agent_qr_cash_out_cash_paid',
                'name'        => 'Agent QR Cash Paid',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'Customer is notified when the agent marks QR cash-out cash handover as paid.',
                'user_type'   => UserType::USER,
                'variables'   => ['agent_name', 'amount', 'reference'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Cash Paid by Agent',
                        'message'   => '{agent_name} marked {amount} cash-out as paid. Reference: {reference}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Cash Paid',
                        'message'   => '{agent_name} marked {amount} as cash paid. Ref: {reference}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Cash paid by {agent_name}. {amount}, ref {reference}.',
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
