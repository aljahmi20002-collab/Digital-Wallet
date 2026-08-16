<?php

namespace Database\Seeders;

use App\Enums\NotificationActionType;
use App\Enums\UserType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class GiftCardNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'identifier'  => 'gift_card_redeemed',
                'name'        => 'Gift Card Redeemed',
                'icon'        => 'card-approved',
                'action_type' => NotificationActionType::COMPLETED,
                'info'        => 'User is notified when a gift card they hold is successfully redeemed into their wallet.',
                'user_type'   => UserType::USER,
                'variables'   => ['amount', 'gift_code', 'trx'],
                'channels'    => [
                    [
                        'channel'   => 'email',
                        'title'     => 'Gift Card Redeemed Successfully',
                        'message'   => 'Your gift card {gift_code} for {amount} has been redeemed and credited to your wallet. Transaction reference: {trx}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'push',
                        'title'     => 'Gift Card Redeemed',
                        'message'   => '{amount} has been added to your wallet from gift card {gift_code}.',
                        'is_active' => true,
                    ],
                    [
                        'channel'   => 'sms',
                        'title'     => null,
                        'message'   => 'Gift card {gift_code} redeemed: {amount} added to your wallet.',
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
