<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\P2P\PaymentAccount;
use App\Models\User;

class P2PPaymentAccountPolicy
{
    public function update(User $user, PaymentAccount $paymentAccount): bool
    {
        return (int) $user->id === (int) $paymentAccount->user_id;
    }

    public function delete(User $user, PaymentAccount $paymentAccount): bool
    {
        return (int) $user->id === (int) $paymentAccount->user_id;
    }
}
