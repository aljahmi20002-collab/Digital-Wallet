<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    private const FALLBACK_THEME_COLOR = '#4663ee';

    private const FALLBACK_BACKGROUND = '#f3f7fb';

    private const FALLBACK_ICONS = [
        'icon_192'         => 'pwa/icons/icon-192.png',
        'icon_512'         => 'pwa/icons/icon-512.png',
        'maskable_icon'    => 'pwa/icons/maskable-512.png',
        'apple_touch_icon' => 'pwa/icons/apple-touch-icon.png',
    ];

    private const ICON_DIMENSIONS = [
        'icon_192'         => [192, 192],
        'icon_512'         => [512, 512],
        'maskable_icon'    => [512, 512],
        'apple_touch_icon' => [180, 180],
    ];

    public function manifest(): JsonResponse|Response
    {
        if (! $this->isPwaEnabled()) {
            return response('Not Found', 404);
        }

        $appName   = $this->appName();
        $shortName = $this->shortName();

        return response()
            ->json([
                'name'        => $appName,
                'short_name'  => $shortName,
                'description' => $this->description($appName),
                'id'          => '/dk-wallet-app',
                // start_url must return 2xx to Chrome's anonymous installability
                // probe; /user/dashboard would 302 → /user/login for that probe.
                // /launch is a public route that always returns 200 and the
                // inline script there bounces the real user to the dashboard.
                'start_url'      => '/launch?source=pwa',
                'scope'          => '/',
                'display'        => $this->display(),
                'orientation'    => $this->orientation(),
                'launch_handler' => [
                    'client_mode' => ['navigate-existing', 'auto'],
                ],
                'background_color'            => $this->backgroundColor(),
                'theme_color'                 => $this->themeColor(),
                'prefer_related_applications' => false,
                'icons'                       => $this->icons(),
            ], 200, [
                'Cache-Control'          => 'public, max-age=600',
                'Content-Type'           => 'application/manifest+json',
                'X-Content-Type-Options' => 'nosniff',
            ], JSON_UNESCAPED_SLASHES);
    }

    public function serviceWorker(): Response
    {
        if (! $this->isPwaEnabled()) {
            return $this->disabledServiceWorker();
        }

        return response()
            ->view('pwa.service-worker', [
                'cacheVersion'       => $this->cacheVersion(),
                'offlineUrl'         => url('/offline'),
                'precacheUrls'       => $this->precacheUrls(),
                'staticPathPrefixes' => $this->staticPathPrefixes(),
                'sensitivePrefixes'  => $this->sensitivePrefixes(),
                'navigationScope'    => '/user/',
            ], 200, [
                'Cache-Control'          => 'no-cache, no-store, must-revalidate',
                'Content-Type'           => 'application/javascript; charset=UTF-8',
                'Service-Worker-Allowed' => '/',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    public function offline(): Response
    {
        if (! $this->isPwaEnabled()) {
            return response('Not Found', 404);
        }

        return response()
            ->view('pwa.offline', [
                'siteTitle'       => $this->appName(),
                'themeColor'      => $this->themeColor(),
                'backgroundColor' => $this->backgroundColor(),
                'iconUrl'         => $this->iconUrl('icon_192'),
                'offlineMessage'  => $this->offlineMessage(),
            ], 200, [
                'Cache-Control'          => 'public, max-age=600',
                'Content-Type'           => 'text/html; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    /**
     * Public PWA launcher — used as the manifest start_url so Chrome's
     * anonymous installability probe always sees a 200 OK. The inline
     * script in the view redirects the actual user on to /user/dashboard
     * once the PWA window has opened.
     */
    public function launcher(Request $request): Response
    {
        if (! $this->isPwaEnabled()) {
            return response('Not Found', 404);
        }

        if ($request->boolean('install')) {
            return $this->installBridge($request);
        }

        return response()
            ->view('pwa.launcher', [
                'siteTitle'       => $this->appName(),
                'themeColor'      => $this->themeColor(),
                'backgroundColor' => $this->backgroundColor(),
                'iconUrl'         => $this->iconUrl('icon_192'),
                'targetUrl'       => route('user.dashboard', [], false),
            ], 200, [
                'Cache-Control'          => 'public, max-age=300',
                'Content-Type'           => 'text/html; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    public function install(Request $request): Response
    {
        return $this->installBridge($request);
    }

    private function installBridge(Request $request): Response
    {
        if (! $this->isPwaEnabled()) {
            return response('Not Found', 404);
        }

        return response()
            ->view('pwa.install', [
                'siteTitle'       => $this->appName(),
                'themeColor'      => $this->themeColor(),
                'backgroundColor' => $this->backgroundColor(),
                'iconUrl'         => $this->iconUrl('icon_192'),
                'returnUrl'       => $this->safeReturnUrl((string) $request->query('return', route('user.dashboard', [], false))),
            ], 200, [
                'Cache-Control'          => 'public, max-age=300',
                'Content-Type'           => 'text/html; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    public function isPwaEnabled(): bool
    {
        return (bool) setting('pwa_enabled', true);
    }

    public function appName(): string
    {
        $custom    = trim((string) setting('pwa_app_name'));
        $siteTitle = trim((string) setting('site_title', config('app.name', 'DigiKash')));

        $name = $custom !== '' ? $custom : $siteTitle;

        return $name !== '' ? $name : 'DigiKash';
    }

    public function shortName(): string
    {
        $custom = trim((string) setting('pwa_short_name'));

        return $custom !== '' ? Str::limit($custom, 12, '') : Str::limit($this->appName(), 12, '');
    }

    public function themeColor(): string
    {
        $value = trim((string) setting('pwa_theme_color'));

        return $this->isValidHexColor($value) ? $value : self::FALLBACK_THEME_COLOR;
    }

    public function backgroundColor(): string
    {
        $value = trim((string) setting('pwa_background_color'));

        return $this->isValidHexColor($value) ? $value : self::FALLBACK_BACKGROUND;
    }

    public function display(): string
    {
        $value   = (string) setting('pwa_display');
        $allowed = ['standalone', 'fullscreen', 'minimal-ui', 'browser'];

        return in_array($value, $allowed, true) ? $value : 'standalone';
    }

    public function orientation(): string
    {
        $value   = (string) setting('pwa_orientation');
        $allowed = ['any', 'portrait-primary', 'landscape-primary', 'portrait', 'landscape'];

        return in_array($value, $allowed, true) ? $value : 'portrait-primary';
    }

    public function iconUrl(string $key): string
    {
        $path = $this->iconPath($key);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset($path);
    }

    private function iconPath(string $key): string
    {
        $fallback = self::FALLBACK_ICONS[$key] ?? '';
        $path     = trim((string) setting('pwa_'.$key));
        $path     = $path !== '' ? $path : $fallback;
        $path     = $this->browserIconPath($path);

        if ($path !== '' && $this->isUsableIcon($path, self::ICON_DIMENSIONS[$key] ?? null)) {
            return $path;
        }

        return $fallback;
    }

    private function browserIconPath(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (is_file(public_path($path))) {
            return $path;
        }

        $storagePath = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        return Storage::disk('public')->exists($storagePath) ? 'storage/'.$storagePath : $path;
    }

    /**
     * @param array{0: int, 1: int}|null $dimensions
     */
    private function isUsableIcon(string $path, ?array $dimensions): bool
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }

        $absolutePath = $this->absoluteIconPath($path);
        if ($absolutePath === null || ! is_file($absolutePath)) {
            return false;
        }

        if ($dimensions === null) {
            return true;
        }

        $imageSize = @getimagesize($absolutePath);

        return is_array($imageSize)
            && (int) $imageSize[0] === $dimensions[0]
            && (int) $imageSize[1] === $dimensions[1];
    }

    private function absoluteIconPath(string $path): ?string
    {
        $path = ltrim($path, '/');

        if (is_file(public_path($path))) {
            return public_path($path);
        }

        $storagePath = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        return Storage::disk('public')->exists($storagePath)
            ? Storage::disk('public')->path($storagePath)
            : null;
    }

    private function description(string $appName): string
    {
        $custom = trim((string) setting('pwa_description'));

        return $custom !== '' ? $custom : 'Secure mobile wallet dashboard for '.$appName.'.';
    }

    private function offlineMessage(): string
    {
        $custom = trim((string) setting('pwa_offline_message'));

        return $custom !== ''
            ? $custom
            : 'A live connection is required for balances, payments, and transactions. Please reconnect and try again.';
    }

    private function cacheVersion(): string
    {
        $appVersion          = (string) config('app.version', '1');
        $manualTag           = trim((string) setting('pwa_cache_version'));
        $assetMtime          = $this->latestAssetMtime();
        $settingsFingerprint = substr(sha1((string) json_encode($this->cacheSettingFingerprint(), JSON_UNESCAPED_SLASHES)), 0, 12);

        $combined = $appVersion.'-'.$assetMtime.'-'.$settingsFingerprint.($manualTag !== '' ? '-'.$manualTag : '');

        return preg_replace('/[^A-Za-z0-9_.-]/', '-', $combined) ?: '1';
    }

    /**
     * @return array<string, string>
     */
    private function cacheSettingFingerprint(): array
    {
        return [
            'app_name'         => $this->appName(),
            'short_name'       => $this->shortName(),
            'description'      => $this->description($this->appName()),
            'theme_color'      => $this->themeColor(),
            'background_color' => $this->backgroundColor(),
            'display'          => $this->display(),
            'orientation'      => $this->orientation(),
            'icon_192'         => $this->iconPath('icon_192'),
            'icon_512'         => $this->iconPath('icon_512'),
            'maskable_icon'    => $this->iconPath('maskable_icon'),
            'apple_touch_icon' => $this->iconPath('apple_touch_icon'),
            'offline_message'  => $this->offlineMessage(),
        ];
    }

    private function latestAssetMtime(): int
    {
        $candidates = [
            'general/css/common.css',
            'general/js/helpers.js',
            'frontend/js/pwa.js',
            'frontend/js/dashboard-mobile-app.js',
            'frontend/css/dashboard-mobile-app.css',
            'frontend/css/dashboard-style.css',
        ];

        $latest = 0;
        foreach ($candidates as $relative) {
            $absolute = public_path($relative);
            if (is_file($absolute)) {
                $latest = max($latest, (int) filemtime($absolute));
            }
        }

        return $latest > 0 ? $latest : time();
    }

    private function isValidHexColor(string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
    }

    private function safeReturnUrl(string $url): string
    {
        $fallback = route('user.dashboard', [], false);
        $url      = trim($url);

        if ($url === '' || str_starts_with($url, '//') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            return $fallback;
        }

        return str_starts_with($url, '/') ? $url : $fallback;
    }

    private function disabledServiceWorker(): Response
    {
        $script = <<<'JS'
self.addEventListener("install", function () { self.skipWaiting(); });
self.addEventListener("activate", function (event) {
    event.waitUntil((async function () {
        const keys = await caches.keys();
        await Promise.all(keys
            .filter(function (key) { return key.indexOf("digikash-pwa") === 0; })
            .map(function (key) { return caches.delete(key); }));
        if (self.registration && self.registration.unregister) {
            await self.registration.unregister();
        }
        const clients = await self.clients.matchAll({ type: "window" });
        clients.forEach(function (client) {
            try { client.navigate(client.url); } catch (e) {}
        });
    })());
});
JS;

        return response($script, 200, [
            'Cache-Control'          => 'no-cache, no-store, must-revalidate',
            'Content-Type'           => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array<int, array{src: string, sizes: string, type: string, purpose?: string}>
     */
    private function icons(): array
    {
        return [
            [
                'src'     => $this->iconUrl('icon_192'),
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src'     => $this->iconUrl('icon_512'),
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src'     => $this->iconUrl('maskable_icon'),
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'maskable',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function precacheUrls(): array
    {
        return [
            url('/offline'),
            $this->iconUrl('icon_192'),
            $this->iconUrl('icon_512'),
            $this->iconUrl('maskable_icon'),
            $this->iconUrl('apple_touch_icon'),
            asset('general/css/bootstrap.min.css'),
            asset('general/css/fontawesome.min.css'),
            asset('general/css/simple-notify.min.css'),
            asset('general/css/common.css?v='.$this->publicFileVersion('general/css/common.css')),
            asset('general/css/daterangepicker.css'),
            asset('frontend/css/_variables.css?v='.config('app.version')),
            asset('frontend/css/dashboard-style.css?v='.config('app.version')),
            asset('frontend/css/dashboard-responsive.css?v='.config('app.version')),
            asset('frontend/css/premium-header.css?v='.config('app.version')),
            asset('frontend/css/dashboard-mobile-app.css?v='.$this->publicFileVersion('frontend/css/dashboard-mobile-app.css')),
            asset('frontend/js/jquery-3.7.1.min.js'),
            asset('general/js/bootstrap.bundle.min.js'),
            asset('general/js/simple-notify.min.js'),
            asset('general/js/helpers.js?v='.$this->publicFileVersion('general/js/helpers.js')),
            asset('frontend/js/dashboard-main.js'),
            asset('frontend/js/dashboard-mobile-app.js?v='.$this->publicFileVersion('frontend/js/dashboard-mobile-app.js')),
            asset('frontend/js/pwa.js?v='.$this->publicFileVersion('frontend/js/pwa.js')),
        ];
    }

    private function publicFileVersion(string $path): string
    {
        $publicPath = public_path($path);

        return config('app.version').'-'.(is_file($publicPath) ? filemtime($publicPath) : '1');
    }

    /**
     * @return array<int, string>
     */
    private function staticPathPrefixes(): array
    {
        return [
            '/frontend/css/',
            '/frontend/js/',
            '/general/css/',
            '/general/js/',
            '/general/static/',
            '/general/webfonts/',
            '/pwa/',
            '/images/',
            '/storage/images/',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function sensitivePrefixes(): array
    {
        return [
            '/admin',
            '/api',
            '/currency-rate',
            '/file/download',
            '/ipn',
            '/payment',
            '/payment-link',
            '/summernote',
            '/user/notifications/recent',
            '/user/wallet/currency-info',
            '/user/wallet/info',
            '/user/wallet/supported-payment-methods',
            '/user/wallet/validate-recipient',
            '/webhooks',
        ];
    }
}
