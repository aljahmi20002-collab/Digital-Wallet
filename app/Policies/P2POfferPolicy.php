<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\P2P\OfferStatus;
use App\Models\P2P\Offer;
use App\Models\User;

class P2POfferPolicy
{
    public function view(?User $user, Offer $offer): bool
    {
        if ($offer->status === OfferStatus::ACTIVE) {
            return true;
        }

        return $user !== null && (int) $user->id === (int) $offer->user_id;
    }

    public function update(User $user, Offer $offer): bool
    {
        return (int) $user->id === (int) $offer->user_id;
    }

    public function delete(User $user, Offer $offer): bool
    {
        return (int) $user->id === (int) $offer->user_id;
    }
}
