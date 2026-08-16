<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\FeatureManager;
use Illuminate\Support\Facades\Route;

class ControlPanelManager
{
    public static function definitions(): array
    {
        return config('control_panel.sections', []);
    }

    public static function build(array $adminPermissions = []): array
    {
        $sections = [];

        foreach (self::definitions() as $sectionKey => $section) {
            $items = [];

            foreach ((array) data_get($section, 'items', []) as $itemKey => $item) {
                if (! self::shouldIncludeItem($item, $adminPermissions)) {
                    continue;
                }

                $url = self::resolveRouteUrl($item);

                if ($url === null) {
                    continue;
                }

                $items[] = [
                    'code'        => (string) $itemKey,
                    'label'       => (string) data_get($item, 'label', str($itemKey)->headline()->toString()),
                    'description' => (string) data_get($item, 'description', 'Administrative shortcut'),
                    'route'       => (string) data_get($item, 'route', ''),
                    'url'         => $url,
                    'permission'  => data_get($item, 'permission'),
                    'icon'        => (string) data_get($item, 'icon', data_get($section, 'icon', 'apps-1')),
                    'color'       => (string) data_get($item, 'color', data_get($section, 'color', 'secondary')),
                    'badge'       => data_get($item, 'badge'),
                    'keywords'    => array_values((array) data_get($item, 'keywords', [])),
                ];
            }

            if ($items === []) {
                continue;
            }

            $sections[] = [
                'code'        => (string) $sectionKey,
                'label'       => (string) data_get($section, 'label', str($sectionKey)->headline()->toString()),
                'description' => (string) data_get($section, 'description', 'Priority administrative shortcuts'),
                'icon'        => (string) data_get($section, 'icon', 'apps-1'),
                'color'       => (string) data_get($section, 'color', 'secondary'),
                'features'    => $items,
            ];
        }

        return $sections;
    }

    private static function shouldIncludeItem(array $item, array $adminPermissions): bool
    {
        $permission = data_get($item, 'permission');

        if (is_string($permission) && $permission !== '' && ! in_array($permission, $adminPermissions, true)) {
            return false;
        }

        $featureKey = data_get($item, 'feature_key');

        if (is_string($featureKey) && $featureKey !== '' && ! app(FeatureManager::class)->isEnabled($featureKey)) {
            return false;
        }

        $route = data_get($item, 'route');

        return is_string($route) && $route !== '' && Route::has($route);
    }

    private static function resolveRouteUrl(array $item): ?string
    {
        $route = (string) data_get($item, 'route', '');

        if ($route === '' || ! Route::has($route)) {
            return null;
        }

        return route($route, (array) data_get($item, 'params', []));
    }
}
