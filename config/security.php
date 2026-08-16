<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | This file holds security-related configurations for the application.
    | */
    'duplicate_submission_timeout'       => env('DUPLICATE_SUBMISSION_TIMEOUT', 10),
    'secure_response_headers'            => env('SECURE_RESPONSE_HEADERS', true),
    'strict_transport_security'          => env('STRICT_TRANSPORT_SECURITY', true),
    'login_attempt_limit'                => env('LOGIN_ATTEMPT_LIMIT', 5),
    'login_lock_minutes'                 => env('LOGIN_LOCK_MINUTES', 15),
    'wallet_pin_attempt_limit'           => env('WALLET_PIN_ATTEMPT_LIMIT', 5),
    'wallet_pin_lock_minutes'            => env('WALLET_PIN_LOCK_MINUTES', 15),
    'merchant_api_signature_required'    => env('MERCHANT_API_SIGNATURE_REQUIRED', true),
    'merchant_api_timestamp_tolerance'   => env('MERCHANT_API_TIMESTAMP_TOLERANCE', 300),
    'merchant_api_rate_limit_per_minute' => env('MERCHANT_API_RATE_LIMIT_PER_MINUTE', 120),
];
