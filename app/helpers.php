<?php

use App\Enums\Theme;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Models\Page;
use App\Models\Plugin;
use App\Models\Setting;
use App\Services\CurrencyService;
use App\Support\InstallationManager;
use App\Support\NotificationTuneLibrary;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * Determine if the current route matches a given route(s) and return a CSS class.
 *
 * @param  array|string $route     The route name(s) to check.
 * @param  mixed        $parameter Optional parameter for full URL comparison.
 * @param  string       $class     The CSS class to return if active. Default is 'active'.
 * @return string       The CSS class if the route is active, otherwise an empty string.
 */
if (! function_exists('isActive')) {
    function isActive(array|string $route, mixed $parameter = null, string $class = 'active', array $except_params = []): string
    {
        $currentRouteName = Route::currentRouteName();

        // Check route match
        $matchRoute = is_array($route)
            ? in_array($currentRouteName, $route)
            : $currentRouteName === $route;

        if (! $matchRoute) {
            return '';
        }

        // If no parameter check needed
        if (empty($parameter) || ! is_array($parameter)) {
            return $class;
        }

        // Compare each passed parameter
        foreach ($parameter as $key => $value) {
            if (in_array($key, $except_params)) {
                continue;
            }

            if (request()->query($key) !== $value && request()->route($key) !== $value) {
                return '';
            }
        }

        return $class;
    }
}

/**
 * Convert a given title to a properly formatted and transliterated string.
 *
 * @param  string $title The title to format.
 * @return string The formatted and transliterated title.
 */
if (! function_exists('title')) {
    function title($title): string
    {
        return Str::of($title)
            ->lower()
            ->replace(['_', '-'], ' ')
            ->title()
            ->transliterate();
    }
}

/**
 * Retrieve a setting value from the database.
 *
 * @param  mixed $key     The setting key to fetch. If null, returns a new Setting instance.
 * @param  mixed $default Default value if setting is not found. Default is null.
 * @return mixed The setting value or default value.
 */
if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_null($key)) {
            return new Setting;
        }

        try {
            if (! app(InstallationManager::class)->settingsTableAvailable()) {
                return value($default);
            }
        } catch (Throwable) {
            return value($default);
        }

        if (is_array($key)) {
            return Setting::set($key[0], $key[1]);
        }

        try {
            return Setting::get($key, null, $default) ?? value($default);
        } catch (Throwable) {
            return value($default);
        }
    }
}

/**
 * Resolve the currently-active site theme. Drives both the public
 * Blade layout/section partials and the page-builder component pool.
 *
 * Reads `active_theme` from the settings table (string value) and coerces
 * it to a {@see Theme} case, falling back to Classic when the
 * setting is missing or unrecognised.
 */
if (! function_exists('activeTheme')) {
    function activeTheme(): Theme
    {
        try {
            $value = setting('active_theme', Theme::default()->value);
        } catch (Throwable) {
            return Theme::default();
        }

        return Theme::fromValueOrDefault($value);
    }
}

/**
 * Resolve the theme-appropriate Blade view name.
 *
 * When the Golden theme is active, attempts to find a parallel view under a
 * "golden" namespace by inserting "golden" after the second path segment
 * (e.g. `frontend.auth.user.login` → `frontend.auth.golden.user.login`,
 * `frontend.auth.verify-email` → `frontend.auth.golden.verify-email`). Falls
 * back to the original (classic) view if the golden variant doesn't exist —
 * this keeps screens we haven't re-skinned yet rendering normally.
 */
if (! function_exists('themedView')) {
    function themedView(string $view): string
    {
        if (! activeTheme()->isDark()) {
            return $view;
        }

        $segments = explode('.', $view);
        if (count($segments) < 3 || $segments[0] !== 'frontend') {
            return $view;
        }

        $themedSegments = $segments;
        array_splice($themedSegments, 2, 0, ['golden']);
        $themed = implode('.', $themedSegments);

        return view()->exists($themed) ? $themed : $view;
    }
}

/**
 * Retrieve credentials for a given plugin.
 *
 * @param  string $pluginCode The plugin's unique identifier.
 * @return mixed  The credentials associated with the plugin.
 */
if (! function_exists('pluginCredentials')) {
    function pluginCredentials($pluginCode)
    {
        if (app()->environment('testing')) {
            try {
                if (! Schema::hasTable('plugins') || ! Schema::hasColumn('plugins', 'code')) {
                    return [];
                }
            } catch (Throwable) {
                return [];
            }
        }

        try {
            if (! Schema::hasTable('plugins') || ! Schema::hasColumn('plugins', 'code')) {
                return [];
            }
        } catch (Throwable) {
            return [];
        }

        return Plugin::credentials($pluginCode);
    }
}

/**
 * Flash a notification message to the session.
 *
 * @param string $type    The notification type (e.g., 'success', 'error').
 * @param string $message The message content.
 */
if (! function_exists('notifyEvs')) {
    function notifyEvs($type, $message): void
    {
        session()->flash('notifyevs', ['type' => $type, 'message' => $message]);
    }
}

/**
 * Retrieve selection options for a given type.
 *
 * @param  string $type The type of options to fetch.
 * @return array  The array of available options.
 */
if (! function_exists('setting_select_options')) {
    function setting_select_options(string $type): array
    {

        $formattedTimeZones = collect(getJsonData('time_zone'))->mapWithKeys(function ($item) {
            return ["{$item['utc']} - {$item['name']}" => $item['zone']];
        })->toArray();

        return match ($type) {
            'mail_secure'               => ['ssl', 'tls'],
            'site_environment'          => ['local', 'production'],
            'site_timezone'             => $formattedTimeZones ?? [],
            'home_redirect'             => array_merge(Page::getSlugs(), ['user/login']),
            'site_decimal'              => [0, 1, 2, 3, 4, 5, 6, 7, 8],
            'pwa_display'               => ['standalone', 'fullscreen', 'minimal-ui', 'browser'],
            'pwa_orientation'           => ['portrait-primary', 'landscape-primary', 'portrait', 'landscape', 'any'],
            'notification_tune_default' => collect(NotificationTuneLibrary::tunes())
                ->mapWithKeys(fn (array $tune, string $key): array => [$tune['label'] => $key])
                ->all(),
            default => [],
        };
    }
}

/**
 * Read and decode JSON data from a specified file.
 *
 * @param  string $fileName The name of the JSON file (without extension).
 * @return array  The decoded JSON data as an associative array.
 *
 * @throws Exception If the file is not found or cannot be read.
 */
if (! function_exists('getJsonData')) {
    function getJsonData(string $fileName): array
    {
        $filePath = resource_path("json/{$fileName}.json");

        if (! file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new Exception("Could not read file: {$filePath}");
        }

        return json_decode($jsonContent, true);
    }
}

/**
 * Retrieve the default site currency.
 *
 * @param  string $type The type of currency data to return (e.g., 'code', 'symbol').
 * @return mixed  The currency data or null if not found.
 */
if (! function_exists('siteCurrency')) {
    function siteCurrency($type = 'code')
    {
        try {
            return app(CurrencyService::class)->getDefaultCurrency()[$type] ?? null;
        } catch (Throwable) {
            return null;
        }
    }
}

/**
 * Retrieve the currency symbol for a given currency code.
 *
 * @param  string      $currencyCode The currency code.
 * @return string|null The corresponding currency symbol or null if not found.
 */
if (! function_exists('getSymbol')) {
    function getSymbol($currencyCode): ?string
    {
        return app(CurrencyService::class)->getCurrencyByCode($currencyCode)['symbol'] ?? null;
    }
}

/**
 * Load and cache the list of countries from a JSON file.
 *
 * @return array The cached country list.
 */
if (! function_exists('getCountries')) {
    function getCountries(): array
    {
        return Cache::rememberForever('countries', function () {
            $path = resource_path('json/country_codes.json');

            return File::exists($path) ? json_decode(File::get($path), true) : [];
        });
    }
}

if (! function_exists('getCountryFlagEmoji')) {
    function getCountryFlagEmoji(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if (! preg_match('/^[A-Z]{2}$/', $code)) {
            return '';
        }

        $base = 127397;

        return mb_chr($base + ord($code[0]), 'UTF-8').mb_chr($base + ord($code[1]), 'UTF-8');
    }
}

/**
 * Retrieve country details by country code.
 *
 * @param  string     $code The country code.
 * @return array|null The country details or null if not found.
 */
if (! function_exists('getCountryByCode')) {
    function getCountryByCode($code): ?array
    {
        return collect(getCountries())->firstWhere('code', strtoupper($code));
    }
}

if (! function_exists('getCountryDisplayLabel')) {
    function getCountryDisplayLabel(?string $code, bool $withFlag = true): ?string
    {
        $country = getCountryByCode((string) $code);
        if (! $country) {
            return null;
        }

        $name     = title((string) ($country['name'] ?? ''));
        $dialCode = (string) ($country['dial_code'] ?? '');
        $label    = trim($name.($dialCode !== '' ? ' ('.$dialCode.')' : ''));

        if (! $withFlag) {
            return $label;
        }

        $flag = getCountryFlagEmoji((string) ($country['code'] ?? ''));

        return trim(($flag !== '' ? $flag.' ' : '').$label);
    }
}

/**
 * Retrieve country details by dial code.
 *
 * @param  string     $dialCode The dial code.
 * @return array|null The country details or null if not found.
 */
if (! function_exists('getCountryByDialCode')) {
    function getCountryByDialCode($dialCode): ?array
    {
        return collect(getCountries())->firstWhere('dial_code', $dialCode);
    }
}

/**
 * Retrieve the current location based on the user's IP address.
 *
 * @return array The location data including country code, name, dial code, and IP.
 *
 * @throws Exception If the location cannot be determined, returns an array with null values.
 */
if (! function_exists('getLocation')) {
    function getLocation(): Fluent
    {
        $clientIp = request()->ip();
        $ip       = in_array($clientIp, ['127.0.0.1', '::1']) ? '8.8.8.8' : $clientIp;

        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful()) {
                $locationData   = $response->json();
                $currentCountry = getCountryByCode($locationData['countryCode'] ?? null);

                return new Fluent([
                    'country_code' => $currentCountry['code']      ?? null,
                    'name'         => $currentCountry['name']      ?? 'Unknown',
                    'dial_code'    => $currentCountry['dial_code'] ?? null,
                    'ip'           => $locationData['query']       ?? $ip,
                ]);
            } else {
                Log::warning('IP API request failed', ['ip' => $ip, 'status' => $response->status()]);
            }
        } catch (Exception $e) {
            Log::error('IP API error', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return new Fluent([
            'country_code' => null,
            'name'         => 'Unknown',
            'dial_code'    => null,
            'ip'           => $ip,
        ]);
    }
}

/**
 * Retrieve country name and dial code by country code.
 *
 * @param  string     $code The country code (e.g., 'AF' for Afghanistan)
 * @return array|null Returns an array with 'name' and 'dial_code' if found, otherwise null.
 */
if (! function_exists('getCountryDataByCode')) {
    function getCountryDataByCode($code)
    {
        return getCountryByCode((string) $code);
    }
}

if (! function_exists('formatPhone')) {
    function formatPhone(string $countryCode, string $rawPhone): string
    {
        $dialCode    = preg_replace('/\D+/', '', $countryCode); // remove non-numeric
        $phoneNumber = preg_replace('/\D+/', '', $rawPhone); // clean input

        return '+'.$dialCode.$phoneNumber;
    }
}

/**
 * Retrieve feature statistics for a given transaction type and time period.
 *
 * This function calculates and caches statistics for completed, pending, and failed transactions
 * of a specified type over a given number of days. The results are returned as an array of
 * associative arrays, each containing the title, value, value change, icon, and color class.
 *
 * @param  TrxType $trxType The type of transaction to retrieve statistics for.
 * @param  int     $days    The number of days to calculate statistics over. Default is 7.
 * @return array   An array of statistics for completed, pending, and failed transactions.
 */
if (! function_exists('featureStatistics')) {

    function featureStatistics(TrxType $trxType, $days = 7): array
    {
        $userId   = auth()->user()->id;
        $cacheTtl = now()->addMinutes(5); // Cache lifetime of 5 minutes

        // Cache key for completed stats
        $cacheKeyCompleted = "feature_stats:{$userId}:{$trxType->value}:completed:{$days}";
        $completedStats    = Cache::remember($cacheKeyCompleted, $cacheTtl, function () use ($trxType, $userId, $days) {
            return Transaction::calculateTransactionTypeStatistics(
                $trxType,
                TrxStatus::COMPLETED,
                $userId,
                $days
            );
        });

        // Cache key for pending stats
        $cacheKeyPending = "feature_stats:{$userId}:{$trxType->value}:pending:{$days}";
        $pendingStats    = Cache::remember($cacheKeyPending, $cacheTtl, function () use ($trxType, $userId, $days) {
            return Transaction::calculateTransactionTypeStatistics(
                $trxType,
                TrxStatus::PENDING,
                $userId,
                $days
            );
        });

        // Cache key for failed stats
        $cacheKeyFailed = "feature_stats:{$userId}:{$trxType->value}:failed:{$days}";
        $failedStats    = Cache::remember($cacheKeyFailed, $cacheTtl, function () use ($trxType, $userId, $days) {
            return Transaction::calculateTransactionTypeStatistics(
                $trxType,
                [TrxStatus::FAILED, TrxStatus::CANCELED],
                $userId,
                $days
            );
        });

        return [
            [
                'title'        => __('Completed :type', ['type' => $trxType->label()]),
                'value'        => $completedStats['current_value'],
                'value_change' => $completedStats['current_value'] - $completedStats['previous_value'],
                'icon'         => 'complete',
                'color_class'  => 'success-svg',
            ],
            [
                'title'        => __('Pending :type', ['type' => $trxType->label()]),
                'value'        => $pendingStats['current_value'],
                'value_change' => $pendingStats['current_value'] - $pendingStats['previous_value'],
                'icon'         => 'pending',
                'color_class'  => 'info-svg',
            ],
            [
                'title'        => __('Failed :type', ['type' => $trxType->label()]),
                'value'        => $failedStats['current_value'],
                'value_change' => $failedStats['current_value'] - $failedStats['previous_value'],
                'icon'         => 'failed',
                'color_class'  => 'danger-svg',
            ],
        ];
    }
}

/**
 * Generate user avatar details based on the first and last name.
 *
 * This function extracts the first letter of the first name
 * and the last letter of the last name, converts them to uppercase,
 * and generates a corresponding background class for styling.
 *
 * @param  string $first_name The user's first name.
 * @param  string $last_name  The user's last name.
 * @return array  An associative array containing:
 *                - 'initials' => Combined initials (e.g., "JD" for John Doe).
 *                - 'class' => CSS class for avatar background (e.g., "avatar-bg-j").
 */
if (! function_exists('getUserAvatarDetails')) {
    function getUserAvatarDetails(?string $first_name, ?string $last_name = null): array
    {
        // Handle cases where only a full name is provided in the first parameter
        if ($last_name === null && ! empty($first_name)) {
            $nameParts  = explode(' ', trim($first_name), 2);
            $first_name = $nameParts[0] ?? '';
            $last_name  = $nameParts[1] ?? '';
        }

        // Extract initials
        $firstLetter = ! empty($first_name) ? strtoupper(substr($first_name, 0, 1)) : '';
        $lastLetter  = ! empty($last_name) ? strtoupper(substr($last_name, 0, 1)) : '';

        // Determine avatar background class
        $avatarBgClass = 'avatar-bg-'.strtolower($firstLetter ?: 'default');

        return [
            'initials' => $firstLetter.$lastLetter,
            'class'    => $avatarBgClass,
        ];
    }

}

if (! function_exists('safeUrl')) {
    /**
     * Sanitise an admin-or-user-supplied URL for safe use inside an
     * href attribute. Blocks `javascript:`, `data:`, `vbscript:`, and
     * other unsafe schemes that turn a click into JS execution.
     *
     * Use this whenever the URL came from the database (settings,
     * footer links, notification action_link, merchant site_url, etc.)
     * — anywhere `{{ }}` alone is not enough because the danger is in
     * the scheme, not in HTML entities.
     */
    function safeUrl(?string $url, string $fallback = '#'): string
    {
        if (blank($url)) {
            return $fallback;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return $fallback;
        }

        // Browsers strip ASCII control characters (tab, newline, CR, NUL,
        // etc.) from URLs before parsing the scheme, so an attacker can
        // smuggle a dangerous scheme as `java\tscript:` and have it execute.
        // We must do the same stripping BEFORE the scheme check, otherwise
        // we wave the payload straight through.
        $stripped = preg_replace('/[\x00-\x1F\x7F]+/', '', $trimmed) ?? '';

        if (preg_match('#^(javascript|data|vbscript|file|about|chrome|jar|view-source)\s*:#i', $stripped)) {
            return $fallback;
        }

        // Hand back the cleaned version, not the original — so the browser
        // can never see the smuggled control characters either.
        return $stripped;
    }
}

if (! function_exists('demoProtectedEmails')) {
    /**
     * Canonical list of seeded demo-account emails.
     *
     * These accounts (users, merchants, agents) are the shared sandbox
     * logins published on the CodeCanyon demo install. They must stay
     * usable for every evaluator, so when APP_DEMO=true these emails
     * are forbidden from editing their own email / password / 2FA, etc.
     *
     * Anything else (real accounts the demo owner created for their
     * own testing) is unaffected.
     *
     * @return array<int, string>
     */
    function demoProtectedEmails(): array
    {
        return [
            // Customers
            'ayesha.rahman@digikash.test',
            'imran.hossain@digikash.test',
            // Merchants
            'nusrat.farhana@digikash.test',
            'tanvir.ahmed@digikash.test',
            // Agents
            'farid.uddin@digikash.test',
            'sabila.akter@digikash.test',
        ];
    }
}

if (! function_exists('isDemoProtectedAccount')) {
    /**
     * Should this account be blocked from changing its own
     * credentials right now?
     *
     * Returns true only when BOTH conditions hold:
     *   • the runtime has demo mode enabled (APP_DEMO=true in env);
     *   • the email belongs to the seeded demo-account allowlist.
     *
     * The seller's own admin or any non-demo user is never blocked,
     * even in demo mode — guards using this helper protect only the
     * shared sandbox credentials.
     */
    function isDemoProtectedAccount(?string $email): bool
    {
        if (blank($email)) {
            return false;
        }

        if (! (bool) config('app.demo', false)) {
            return false;
        }

        return in_array(strtolower(trim($email)), demoProtectedEmails(), true);
    }
}

if (! function_exists('highlightWord')) {
    function highlightWord($text, $word = 'money', $class = 'highlight')
    {
        $escapedText = e($text);

        return preg_replace_callback(
            '/\b('.preg_quote($word, '/').')\b/i',
            function ($matches) use ($class) {
                return '<span class="'.e($class).'">'.e($matches[1]).'</span>';
            },
            $escapedText
        );
    }
}

if (! function_exists('__safe')) {
    function __safe($key, $replace = [], $locale = null)
    {
        $translation = __($key, $replace, $locale);

        return is_string($translation) ? $translation : $key;
    }
}

if (! function_exists('getAdminMenuByCode')) {
    function getAdminMenuByCode(string $code)
    {
        $menus = config('admin_menus');

        foreach ($menus as $group) {
            if (! isset($group['menus'])) {
                continue;
            }

            foreach ($group['menus'] as $menu) {
                if (isset($menu['code']) && $menu['code'] === $code) {
                    return $menu;
                }
            }
        }

        return null;
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency(float $amount): string
    {
        $symbol = siteCurrency('symbol'); // returns $, ৳ etc.

        return $symbol.number_format($amount, 2);
    }
}

if (! function_exists('str_replace_placeholders')) {
    function str_replace_placeholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{'.$key.'}', $value, $template);
        }

        return $template;
    }
}

if (! function_exists('maskSensitive')) {
    function maskSensitive($value, $type = 'email')
    {
        if (config('app.demo')) {
            if ($type === 'email') {
                return preg_replace('/^(.).+(@.+)$/', '$1****$2', $value);
            } elseif ($type === 'phone') {
                return substr($value, 0, 3).'****'.substr($value, -2);
            }
        }

        return $value;
    }

}
