<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SignupBonusController extends Controller
{
    /**
     * Mark the signup-bonus popup as acknowledged for the current user.
     *
     * Idempotent: once `signup_bonus_seen_at` is set, subsequent calls
     * are no-ops. Always returns success — the popup must close
     * cleanly on the client even when the user has already
     * acknowledged it elsewhere.
     */
    public function acknowledge(): JsonResponse
    {
        $user = auth()->user();

        if ($user && $user->signup_bonus_seen_at === null) {
            $user->forceFill(['signup_bonus_seen_at' => now()])->save();
        }

        return response()->json(['success' => true]);
    }
}
