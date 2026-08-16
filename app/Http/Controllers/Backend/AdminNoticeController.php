<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNoticeController extends Controller
{
    /**
     * Whitelist of dashboard-notice keys the dismiss endpoint will accept.
     * Anything outside this list is rejected so we never store arbitrary
     * keys submitted by a logged-in admin or a leaked CSRF token.
     */
    private const KNOWN_NOTICES = [
        'scheduler-and-queue-setup',
    ];

    /**
     * Permanently dismiss a dashboard notice for the current admin.
     *
     * Stores the key in the admin's `dismissed_notices` JSON column so
     * the banner never re-appears for them on any future login or device.
     */
    public function dismiss(Request $request, string $key): JsonResponse
    {
        if (! in_array($key, self::KNOWN_NOTICES, true)) {
            return response()->json([
                'ok'      => false,
                'message' => __('Unknown notice.'),
            ], 422);
        }

        $admin = $request->user('admin');
        if ($admin === null) {
            return response()->json([
                'ok'      => false,
                'message' => __('Unauthenticated.'),
            ], 401);
        }

        $admin->dismissNotice($key);

        return response()->json(['ok' => true]);
    }
}
