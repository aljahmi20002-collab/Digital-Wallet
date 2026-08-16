<?php

namespace App\Services;

use App\Constants\Status;
use App\Models\Language;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AppConfigService
{
    /**
     * Apply application-wide settings dynamically.
     */
    public function applyAppSettings(): void
    {
        $defaultLanguage     = Language::default();
        $defaultLanguageCode = $defaultLanguage->code ?? 'en';
        Config::set([
            'app.timezone'                                => setting('site_timezone', 'utc'),
            'app.env'                                     => setting('site_environment', 'local'),
            'app.debug'                                   => setting('development_mode', true),
            'app.locale'                                  => $defaultLanguageCode,
            'app.default_language'                        => $defaultLanguageCode,
            'app.default_currency'                        => siteCurrency(),
            'app.default_currency_symbol'                 => siteCurrency('symbol'),
            'security.duplicate_submission_timeout'       => setting('submission_lock_duration', 5),
            'security.secure_response_headers'            => setting('secure_response_headers', true),
            'security.strict_transport_security'          => setting('strict_transport_security', true),
            'security.login_attempt_limit'                => setting('login_attempt_limit', 5),
            'security.login_lock_minutes'                 => setting('login_lock_minutes', 15),
            'security.wallet_pin_attempt_limit'           => setting('wallet_pin_attempt_limit', 5),
            'security.wallet_pin_lock_minutes'            => setting('wallet_pin_lock_minutes', 15),
            'security.merchant_api_signature_required'    => setting('merchant_api_signature_required', true),
            'security.merchant_api_timestamp_tolerance'   => setting('merchant_api_timestamp_tolerance', 300),
            'security.merchant_api_rate_limit_per_minute' => setting('merchant_api_rate_limit_per_minute', 120),
        ]);
    }

    /**
     * Dynamically apply SMTP email settings.
     */
    public function applyMailSettings(): void
    {
        Config::set('mail', [
            'default' => 'smtp',
            'from'    => [
                'name'    => setting('site_title', 'Wallet System'),
                'address' => setting('email_from_address', 'noreply@example.com'),
            ],
            'mailers' => [
                'smtp' => [
                    'transport'  => 'smtp',
                    'host'       => setting('mail_host', 'smtp.example.com'),
                    'port'       => setting('mail_port', 587),
                    'username'   => setting('mail_username', 'user@example.com'),
                    'password'   => setting('mail_password', 'password'),
                    'encryption' => setting('mail_secure', 'tls'),
                ],
            ],
        ]);
    }

    public function applySmsConfig(): void
    {
        $twilioConfig = pluginCredentials('twilio');

        if (! isset($twilioConfig['status']) || $twilioConfig['status'] !== Status::TRUE) {
            return;
        }

        Config::set('twilio-notification-channel', [
            'account_sid' => $twilioConfig['account_sid'],
            'auth_token'  => $twilioConfig['auth_token'],
            'from'        => $twilioConfig['from'],
        ]);
    }

    public function applyGoogleReCaptchaConfig(): void
    {
        $googleReCaptchaCredentials = pluginCredentials('google-recaptcha');

        if (! isset($googleReCaptchaCredentials['status']) || $googleReCaptchaCredentials['status'] !== Status::TRUE) {
            return;
        }

        config()->set([
            'services.recaptcha.key'    => $googleReCaptchaCredentials['recaptcha_key'],
            'services.recaptcha.secret' => $googleReCaptchaCredentials['recaptcha_secret'],
            'services.recaptcha.status' => $googleReCaptchaCredentials['status'],
        ]);
    }

    /**
     * Force HTTPS if enabled.
     */
    public function forceHttpsIfEnabled(): void
    {
        if (config('app.env') !== 'local' && setting('force_https', false)) {
            URL::forceScheme('https');
        }
    }

    /**
     * Ensures the public/storage symlink exists for file uploads.
     * Attempts creation only if missing, logs outcome, never interrupts app.
     */
    public function ensureStorageSymlink(): void
    {
        $link = public_path('storage');

        if (! is_link($link) && ! file_exists($link)) {
            try {
                \Artisan::call('storage:link');
                if (is_link($link) || file_exists($link)) {
                    Log::info('Storage symlink created successfully.');
                } else {
                    Log::warning('Tried to create storage symlink, but it does not exist. Check server permissions.');
                }
            } catch (\Throwable $e) {
                Log::error('Storage symlink creation failed: '.$e->getMessage());
            }
        }
    }
}
