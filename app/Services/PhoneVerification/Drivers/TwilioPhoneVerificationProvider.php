<?php

namespace App\Services\PhoneVerification\Drivers;

use App\Contracts\PhoneVerification\PhoneVerificationProviderInterface;
use App\Exceptions\NotifyErrorException;
use App\Models\User;
use App\Notifications\PhoneVerificationCodeNotification;

class TwilioPhoneVerificationProvider implements PhoneVerificationProviderInterface
{
    public function send(User $user, string $code): void
    {
        if (! config('twilio-notification-channel.enabled')) {
            throw new NotifyErrorException(__('SMS verification provider is disabled.'));
        }

        $user->notify(new PhoneVerificationCodeNotification($code));
    }
}
