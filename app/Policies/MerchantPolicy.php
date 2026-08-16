<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Merchant;
use App\Models\User;

class MerchantPolicy
{
    public function view(User $user, Merchant $merchant): bool
    {
        return (int) $user->id === (int) $merchant->user_id;
    }

    public function update(User $user, Merchant $merchant): bool
    {
        return (int) $user->id === (int) $merchant->user_id;
    }

    public function delete(User $user, Merchant $merchant): bool
    {
        return (int) $user->id === (int) $merchant->user_id;
    }
}
