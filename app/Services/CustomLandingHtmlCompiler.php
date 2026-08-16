<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use JsonException;

class CustomLandingHtmlCompiler
{
    private const BRIDGE_START = '<!-- DIGIKASH_LANDING_BRIDGE_START -->';

    private const BRIDGE_END = '<!-- DIGIKASH_LANDING_BRIDGE_END -->';

    /**
     * @return array<string, array{label: string, route: string, fallback: string, method?: string}>
     */
    public function actions(): array
    {
        return [
            'home' => [
                'label'    => 'Home',
                'route'    => 'home',
                'fallback' => '/',
            ],
            'user-login' => [
                'label'    => 'User Login',
                'route'    => 'user.login',
                'fallback' => '/login',
            ],
            'user-register' => [
                'label'    => 'User Register',
                'route'    => 'user.register',
                'fallback' => '/register',
            ],
            'user-dashboard' => [
                'label'    => 'User Dashboard',
                'route'    => 'user.dashboard',
                'fallback' => '/user/dashboard',
            ],
            'user-wallet' => [
                'label'    => 'User Wallet',
                'route'    => 'user.wallet.index',
                'fallback' => '/user/wallet/list',
            ],
            'user-deposit' => [
                'label'    => 'Deposit Money',
                'route'    => 'user.deposit.create',
                'fallback' => '/user/deposit/create',
            ],
            'user-send-money' => [
                'label'    => 'Send Money',
                'route'    => 'user.send-money.create',
                'fallback' => '/user/send-money/create',
            ],
            'user-support' => [
                'label'    => 'Support Ticket',
                'route'    => 'user.support-ticket.index',
                'fallback' => '/user/support-ticket',
            ],
            'merchant-login' => [
                'label'    => 'Merchant Login',
                'route'    => 'merchant.login',
                'fallback' => '/merchant/login',
            ],
            'merchant-register' => [
                'label'    => 'Merchant Register',
                'route'    => 'merchant.register',
                'fallback' => '/merchant/register',
            ],
            'agent-login' => [
                'label'    => 'Agent Login',
                'route'    => 'agent.login',
                'fallback' => '/agent/login',
            ],
            'agent-register' => [
                'label'    => 'Agent Register',
                'route'    => 'agent.register',
                'fallback' => '/agent/register',
            ],
        ];
    }

    public function compileForPublish(string $html, string $folder): string
    {
        $html = $this->stripBridge($html);
        $html = $this->replacePlaceholders($html, $folder);

        return $this->injectBridge($html, $folder);
    }

    public function stripBridge(string $html): string
    {
        return (string) preg_replace(
            '/\s*'.preg_quote(self::BRIDGE_START, '/').'.*?'.preg_quote(self::BRIDGE_END, '/').'\s*/s',
            "\n",
            $html
        );
    }

    /**
     * @return array<string, string>
     */
    public function placeholders(string $folder): array
    {
        $placeholders = [
            'app_name' => config('app.name', 'DigiKash'),
            'folder'   => $folder,
        ];

        foreach ($this->resolvedActions() as $key => $action) {
            $placeholders[str_replace('-', '_', $key).'_url'] = $action['url'];
        }

        return $placeholders;
    }

    /**
     * @return array<string, array{label: string, url: string, method: string}>
     */
    public function resolvedActions(): array
    {
        return collect($this->actions())
            ->mapWithKeys(fn (array $action, string $key): array => [
                $key => [
                    'label'  => $action['label'],
                    'url'    => $this->routeUrl($action['route'], $action['fallback']),
                    'method' => $action['method'] ?? 'GET',
                ],
            ])
            ->all();
    }

    private function replacePlaceholders(string $html, string $folder): string
    {
        foreach ($this->placeholders($folder) as $key => $value) {
            $html = str_replace(
                ['{'.$key.'}', '{{'.$key.'}}', '{{ '.$key.' }}'],
                $value,
                $html
            );
        }

        return $html;
    }

    private function injectBridge(string $html, string $folder): string
    {
        $bridge = $this->bridgeMarkup($folder);

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $bridge."\n</head>", $html);
        }

        if (str_contains($html, '<body')) {
            return (string) preg_replace('/(<body\b[^>]*>)/i', '$1'."\n".$bridge, $html, 1);
        }

        return $bridge."\n".$html;
    }

    /**
     * @throws JsonException
     */
    private function bridgeMarkup(string $folder): string
    {
        $config = [
            'appName' => config('app.name', 'DigiKash'),
            'folder'  => $folder,
            'actions' => $this->resolvedActions(),
        ];

        $json = json_encode($config, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return self::BRIDGE_START."\n"
            .'<script data-dk-landing-bridge>window.DigiKashLandingBridge = '.$json.';</script>'."\n"
            .'<script defer src="/custom-landings/digikash-landing-bridge.js"></script>'."\n"
            .self::BRIDGE_END;
    }

    private function routeUrl(string $route, string $fallback): string
    {
        return Route::has($route) ? route($route) : url($fallback);
    }
}
