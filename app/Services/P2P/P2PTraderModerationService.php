<?php

declare(strict_types=1);

namespace App\Services\P2P;

use App\Enums\P2P\OfferStatus;
use App\Models\P2P\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class P2PTraderModerationService
{
    /**
     * Suspend a trader from P2P: mark suspended_at + reason, force-disable
     * all ACTIVE offers so no new orders can come in. Existing in-flight
     * orders (PENDING/PAID/DISPUTED) continue to completion and are not
     * touched here.
     */
    public function suspend(User $user, string $reason): void
    {
        DB::transaction(function () use ($user, $reason) {
            $user->forceFill([
                'p2p_trading_suspended_at'   => now(),
                'p2p_trading_suspend_reason' => $reason !== '' ? $reason : null,
            ])->save();

            Offer::query()
                ->where('user_id', $user->id)
                ->where('status', OfferStatus::ACTIVE->value)
                ->update([
                    'status'     => OfferStatus::DISABLED->value,
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * Re-allow a trader to create offers and accept orders again. We do NOT
     * re-activate their disabled offers automatically; the trader must
     * review and re-enable them manually.
     */
    public function reactivate(User $user): void
    {
        $user->forceFill([
            'p2p_trading_suspended_at'   => null,
            'p2p_trading_suspend_reason' => null,
        ])->save();
    }

    public function isSuspended(User $user): bool
    {
        return $user->getAttribute('p2p_trading_suspended_at') !== null;
    }
}
