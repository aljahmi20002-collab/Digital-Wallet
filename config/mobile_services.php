<?php

use App\Services\MobileRecharge\Drivers\HttpMobileRechargeProvider;
use App\Services\MobileRecharge\Drivers\ReloadlyMobileRechargeProvider;
use App\Services\MobileRecharge\Drivers\SandboxMobileRechargeProvider;
use App\Services\PhoneVerification\Drivers\LogPhoneVerificationProvider;
use App\Services\PhoneVerification\Drivers\TwilioPhoneVerificationProvider;

return [
    'phone_verification' => [
        'provider'                => env('PHONE_VERIFICATION_PROVIDER', 'log'),
        'code_length'             => (int) env('PHONE_VERIFICATION_CODE_LENGTH', 6),
        'expires_minutes'         => (int) env('PHONE_VERIFICATION_EXPIRES_MINUTES', 10),
        'resend_cooldown_seconds' => (int) env('PHONE_VERIFICATION_RESEND_COOLDOWN_SECONDS', 60),
        'max_attempts'            => (int) env('PHONE_VERIFICATION_MAX_ATTEMPTS', 5),
        'testing_code'            => env('PHONE_VERIFICATION_TESTING_CODE'),
        'providers'               => [
            'log'    => LogPhoneVerificationProvider::class,
            'twilio' => TwilioPhoneVerificationProvider::class,
        ],
    ],

    'recharge' => [
        'provider'       => env('MOBILE_RECHARGE_PROVIDER', 'sandbox'),
        'min_amount'     => (float) env('MOBILE_RECHARGE_MIN_AMOUNT', 10),
        'max_amount'     => (float) env('MOBILE_RECHARGE_MAX_AMOUNT', 10000),
        'fee_fixed'      => (float) env('MOBILE_RECHARGE_FEE_FIXED', 0),
        'fee_percent'    => (float) env('MOBILE_RECHARGE_FEE_PERCENT', 0),
        'sandbox_status' => env('MOBILE_RECHARGE_SANDBOX_STATUS', 'completed'),
        'providers'      => [
            'sandbox'  => SandboxMobileRechargeProvider::class,
            'http'     => HttpMobileRechargeProvider::class,
            'reloadly' => ReloadlyMobileRechargeProvider::class,
        ],
        'driver_labels' => [
            'sandbox'  => 'Sandbox (Testing)',
            'http'     => 'Generic HTTP API',
            'reloadly' => 'Reloadly (Global Airtime)',
        ],
        'http' => [
            'base_url' => env('MOBILE_RECHARGE_API_BASE_URL'),
            'token'    => env('MOBILE_RECHARGE_API_TOKEN'),
            'timeout'  => (int) env('MOBILE_RECHARGE_API_TIMEOUT', 15),
            'endpoint' => env('MOBILE_RECHARGE_API_ENDPOINT', '/recharges'),
        ],
    ],
];
