<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\SignupBonusService;
use Illuminate\Auth\Events\Verified;

class AwardSignupBonusOnVerified
{
    public function __construct(protected SignupBonusService $bonus) {}

    /**
     * Credit the signup bonus once the user has verified their email,
     * when the program is configured to wait for verification. The
     * service itself guards against double-awards.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        if (! $this->bonus->requiresEmailVerification()) {
            return;
        }

        $this->bonus->award($user);
    }
}
