<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\UpdateSettingRequest;
use App\Services\SettingService;
use Exception;
use Illuminate\Contracts\View\Factory;

class SettingController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index'  => 'site-setting-view',
            'update' => 'site-setting-update',
        ];
    }

    /**
     * Display a listing of the settings.
     *
     * @return Factory
     */
    public function index()
    {
        // Fetch settings from the configuration file
        $settings = config('settings');
        unset($settings['hide_settings']);

        $settings = collect([
            'general_settings',
            'role_branding_settings',
            'site_security',
            'mail_settings',
            'notification_settings',
            'signup_bonus_settings',
            'agent_settings',
            'pwa_settings',
            'cookie_settings',
            'maintenance_mode',
        ])
            ->filter(fn (string $section) => isset($settings[$section]))
            ->mapWithKeys(fn (string $section) => [$section => $settings[$section]])
            ->toArray();

        // Stamp dynamic units that depend on runtime state (e.g. site
        // currency). Done here rather than in config/settings.php
        // because the helper resolves a container service that isn't
        // available during the early config-load phase.
        $settings = $this->stampDynamicUnits($settings);

        // Map over the settings array to extract the 'icon' key from each setting
        $settingMenus = array_map(function ($setting) {
            return $setting['icon'];
        }, $settings);

        // Return the view with the settings and setting menus
        return view('backend.settings.site.index', compact('settings', 'settingMenus'));
    }

    /**
     * Replace any field whose unit is the placeholder `__SITE_CURRENCY__`
     * (or simply needs to mirror the site currency) with the live code
     * from {@see siteCurrency()}. Keeps the rest of the config untouched.
     *
     * @param  array<string, array<string, mixed>> $settings
     * @return array<string, array<string, mixed>>
     */
    protected function stampDynamicUnits(array $settings): array
    {
        $currency = strtoupper((string) (siteCurrency() ?: 'USD'));

        $currencyKeys = [
            'signup_bonus_user_amount',
            'signup_bonus_merchant_amount',
            'signup_bonus_agent_amount',
        ];

        foreach ($settings as $sectionKey => &$section) {
            if (empty($section['elements']) || ! is_array($section['elements'])) {
                continue;
            }

            foreach ($section['elements'] as &$element) {
                if (in_array($element['key'] ?? null, $currencyKeys, true)) {
                    $element['unit'] = $currency;
                }
            }
            unset($element);
        }
        unset($section);

        return $settings;
    }

    public function update($section, UpdateSettingRequest $request, SettingService $settingService)
    {

        try {
            // Update settings using the service
            $settingService->update($section, $request);

            // Build the success message
            $message = __('Settings Updated Successfully');

            // Notify the user of the success
            notifyEvs('success', $message);

            // Redirect back with the section
            return redirect()->back()->with('section', $section);

        } catch (Exception $e) {
            // Notify the user of the error
            notifyEvs('error', $e->getMessage());

            // Redirect back
            return redirect()->back()->with('section', $section);
        }
    }
}
