<?php

namespace App\Contracts\PhoneVerification;

use App\Models\User;

interface PhoneVerificationProviderInterface
{
    public function send(User $user, string $code): void;
}
