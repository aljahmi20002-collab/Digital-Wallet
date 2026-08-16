<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Theme;
use App\Models\Page;
use App\Models\PageComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Theme Manager — flips the site-wide `active_theme` setting between Classic
 * and Golden. The page builder and frontend layout both read this value via
 * {@see activeTheme()} so the entire surface follows in lock-step.
 */
class ThemeManagerController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index'    => 'theme-manager-view',
            'activate' => 'theme-manager-update',
        ];
    }

    public function index(): View
    {
        $activeTheme = activeTheme();

        $themes = collect(Theme::cases())->map(function (Theme $theme) use ($activeTheme) {
            return [
                'value'           => $theme->value,
                'label'           => $theme->label(),
                'tagline'         => $theme->tagline(),
                'preview_image'   => $theme->previewImage(),
                'accent_color'    => $theme->accentColor(),
                'is_dark'         => $theme->isDark(),
                'is_active'       => $theme === $activeTheme,
                'component_count' => PageComponent::query()
                    ->forTheme($theme)
                    ->count(),
            ];
        });

        return view('backend.theme-manager.index', [
            'themes'      => $themes,
            'activeTheme' => $activeTheme,
        ]);
    }

    public function activate(string $theme): RedirectResponse
    {
        $resolved = Theme::tryFrom($theme);

        if ($resolved === null) {
            notifyEvs('error', __('Unknown theme.'));

            return redirect()->route('admin.theme-manager.index');
        }

        if ($resolved === activeTheme()) {
            notifyEvs('info', __(':theme is already active.', ['theme' => $resolved->label()]));

            return redirect()->route('admin.theme-manager.index');
        }

        setting(['active_theme', $resolved->value]);

        // Page components / page slug caches are theme-sensitive — flush them
        // so the next request reflects the new pool of available blocks.
        $this->flushPageCaches();

        notifyEvs('success', __(':theme theme activated. Pages now render with this look.', [
            'theme' => $resolved->label(),
        ]));

        return redirect()->route('admin.theme-manager.index');
    }

    /**
     * Clear every cached Page/PageComponent payload so the new theme's
     * component pool & layout are picked up immediately.
     */
    protected function flushPageCaches(): void
    {
        $slugs   = DB::table('pages')->pluck('slug')->all();
        $pageIds = DB::table('pages')->pluck('id')->all();

        foreach ($slugs as $slug) {
            Cache::forget('page_slug_'.md5((string) $slug));
        }
        foreach ($pageIds as $id) {
            Cache::forget("page_components_{$id}");
        }
        Cache::forget('slugs_list');
    }
}
