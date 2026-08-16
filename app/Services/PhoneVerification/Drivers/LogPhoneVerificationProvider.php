<?php

namespace App\Services\PhoneVerification\Drivers;

use App\Contracts\PhoneVerification\PhoneVerificationProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogPhoneVerificationProvider implements PhoneVerificationProviderInterface
{
    public function send(User $user, string $code): void
    {
        Log::info('Phone verification code generated.', [
            'user_id' => $user->id,
            'phone'   => $user->phone,
            'code'    => $code,
        ]);
    }
}
