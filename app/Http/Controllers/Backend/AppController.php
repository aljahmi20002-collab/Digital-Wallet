<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CustomCodeType;
use App\Models\CustomCode;
use App\Support\ControlPanelManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AppController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'appInfo'                         => 'app-info',
            'controlPanel'                    => 'app-info',
            'styleManager|styleManagerUpdate' => 'style-manager',
            'clearCache'                      => 'app-clear-cache',
            'optimize'                        => 'app-optimize',
            'getMenusForSearch'               => 'app-info', // Allow all admin users to search menus
        ];
    }

    public function appInfo()
    {
        $isDemo = config('app.demo', false);

        // Basic application info (always safe to show)
        $appInfo = [
            'app_version'     => config('app.version'), // You can define this in config
            'laravel_version' => app()->version(),
            'php_version'     => phpversion(),
            'environment'     => app()->environment(),
            'timezone'        => config('app.timezone'),
            'locale'          => config('app.locale'),
            'debug_mode'      => config('app.debug') ? 'Enabled' : 'Disabled',
        ];

        // Server information (sensitive in demo mode)
        $serverInfo = [
            'server_software'     => $isDemo ? 'Hidden (Demo Mode)' : ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
            'server_ip'           => $isDemo ? 'Hidden (Demo Mode)' : ($_SERVER['SERVER_ADDR'] ?? 'Unknown'),
            'host_name'           => $isDemo ? 'Hidden (Demo Mode)' : gethostname(),
            'server_os'           => $isDemo ? 'Hidden (Demo Mode)' : php_uname('s').' '.php_uname('r'),
            'server_architecture' => $isDemo ? 'Hidden (Demo Mode)' : php_uname('m'),
            'web_server'          => $isDemo ? 'Hidden (Demo Mode)' : ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
        ];

        // Database information (sensitive in demo mode)
        $databaseInfo = [
            'database_connection' => $isDemo ? 'Hidden (Demo Mode)' : config('database.default'),
            'database_version'    => $isDemo ? 'Hidden (Demo Mode)' : $this->getDatabaseVersion(),
        ];

        // PHP configuration (some sensitive in demo mode)
        $phpInfo = [
            'php_version'         => phpversion(),
            'php_extensions'      => $isDemo ? 'Hidden (Demo Mode)' : $this->getKeyExtensions(),
            'memory_limit'        => $isDemo ? 'Hidden (Demo Mode)' : ini_get('memory_limit'),
            'max_execution_time'  => $isDemo ? 'Hidden (Demo Mode)' : ini_get('max_execution_time').'s',
            'upload_max_filesize' => $isDemo ? 'Hidden (Demo Mode)' : ini_get('upload_max_filesize'),
            'post_max_size'       => $isDemo ? 'Hidden (Demo Mode)' : ini_get('post_max_size'),
        ];

        // Storage information (sensitive in demo mode)
        $storageInfo = [
            'storage_driver' => $isDemo ? 'Hidden (Demo Mode)' : config('filesystems.default'),
            'cache_driver'   => $isDemo ? 'Hidden (Demo Mode)' : config('cache.default'),
            'queue_driver'   => $isDemo ? 'Hidden (Demo Mode)' : config('queue.default'),
            'session_driver' => $isDemo ? 'Hidden (Demo Mode)' : config('session.driver'),
        ];

        // Security information
        $securityInfo = [
            'app_key_set'     => config('app.key') ? 'Set' : 'Not Set',
            'https_enabled'   => request()->isSecure() ? 'Yes' : 'No',
            'csrf_protection' => 'Enabled',
            'demo_mode'       => $isDemo ? 'Enabled' : 'Disabled',
        ];

        return view('backend.app.info', compact(
            'appInfo',
            'serverInfo',
            'databaseInfo',
            'phpInfo',
            'storageInfo',
            'securityInfo',
            'isDemo'
        ));
    }

    private function getDatabaseVersion()
    {
        try {
            $connection = config('database.default');
            if ($connection === 'mysql') {
                return \DB::select('SELECT VERSION() as version')[0]->version;
            }

            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unable to retrieve';
        }
    }

    private function getKeyExtensions()
    {
        $extensions = ['curl', 'gd', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'zip', 'bcmath', 'ctype', 'fileinfo', 'json'];
        $loaded     = [];

        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                $loaded[] = $ext;
            }
        }

        return count($loaded).' of '.count($extensions).' key extensions loaded';
    }

    public function styleManager()
    {
        $css = CustomCode::ofType(CustomCodeType::CSS)->firstOrNew([
            'type' => CustomCodeType::CSS,
        ], [
            'content' => '',
            'status'  => false,
        ]);

        return view('backend.app.style_manager', compact('css'));
    }

    public function styleManagerUpdate(Request $request)
    {
        $validated = $request->validate([
            'type'    => ['required', Rule::in(CustomCodeType::values())],
            'content' => 'nullable|string',
            'status'  => 'boolean',
        ]);

        CustomCode::updateOrCreate(
            ['type' => $validated['type']],
            [
                'content' => $validated['content'],
                'status'  => $request->boolean('status'),
            ]
        );

        notifyEvs('success', __('Style Manager Updated Successfully'));

        return redirect()->back();
    }

    public function optimize()
    {
        notifyEvs('success', __('Application Optimized Successfully'));
        Artisan::call('app:optimize');

        return redirect()->back();
    }

    public function clearCache()
    {
        notifyEvs('success', __('Cache Cleared Successfully'));
        Artisan::call('app:clear');

        return redirect()->back();
    }

    public function smtpConnectionCheck(Request $request)
    {
        try {
            // Try sending a test email to the authenticated email
            Mail::raw('SMTP Test Email - Connection Successful.', function ($message) use ($request) {
                $message->to($request->input('test_email', config('mail.from.address')))
                    ->subject('SMTP Test Email');
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'SMTP connection successful. Test email sent.',
            ]);
        } catch (\Exception $e) {
            Log::error('SMTP Test Failed: '.$e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'SMTP connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getMenusForSearch(Request $request)
    {
        $menus          = config('admin_menus');
        $flattenedMenus = [];

        foreach ($menus as $section) {
            if (isset($section['menus'])) {
                foreach ($section['menus'] as $menu) {
                    // Handle single menus
                    if ($menu['type'] === 'single' && isset($menu['route'])) {
                        try {
                            $url              = route($menu['route'], $menu['params'] ?? []);
                            $flattenedMenus[] = [
                                'label'      => $menu['label'],
                                'route'      => $menu['route'],
                                'url'        => $url,
                                'icon'       => $menu['icon']       ?? 'cil-puzzle',
                                'params'     => $menu['params']     ?? null,
                                'permission' => $menu['permission'] ?? null,
                                'section'    => $section['label']   ?? 'General',
                            ];
                        } catch (\Exception $e) {
                            // Skip if route doesn't exist
                            continue;
                        }
                    }

                    // Handle group menus with sub_menus
                    if ($menu['type'] === 'groups' && isset($menu['sub_menus'])) {
                        foreach ($menu['sub_menus'] as $subMenu) {
                            if (isset($subMenu['route'])) {
                                try {
                                    $url              = route($subMenu['route'], $subMenu['params'] ?? []);
                                    $flattenedMenus[] = [
                                        'label'      => $subMenu['label'],
                                        'route'      => $subMenu['route'],
                                        'url'        => $url,
                                        'icon'       => $subMenu['icon']       ?? $menu['icon'] ?? 'setting',
                                        'params'     => $subMenu['params']     ?? null,
                                        'permission' => $subMenu['permission'] ?? null,
                                        'section'    => $menu['label'],
                                        'parent'     => $section['label'] ?? 'General',
                                    ];
                                } catch (\Exception $e) {
                                    // Skip if route doesn't exist
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Filter by search query if provided
        $query = $request->get('query', '');
        if (! empty($query)) {
            $flattenedMenus = array_filter($flattenedMenus, function ($menu) use ($query) {
                return stripos($menu['label'], $query) !== false || stripos($menu['section'], $query) !== false || (isset($menu['parent']) && stripos($menu['parent'], $query) !== false);
            });
        }

        // Limit results to prevent overwhelming UI
        $flattenedMenus = array_slice($flattenedMenus, 0, 10);

        return response()->json([
            'success' => true,
            'menus'   => array_values($flattenedMenus),
        ]);
    }

    /**
     * Control Panel - Quick access to all admin features
     */
    public function controlPanel()
    {
        $adminPermissions = session('admin_permissions', []);
        $controlPanelData = ControlPanelManager::build($adminPermissions);

        return view('backend.app.control-panel', compact('controlPanelData'));
    }

    /**
     * Get feature description based on label
     */
    private function getFeatureDescription($label)
    {
        $descriptions = [
            'Dashboard'              => 'Overview and analytics',
            'All Users'              => 'Manage user accounts',
            'Active Users'           => 'View active users',
            'Suspended Users'        => 'Manage suspended accounts',
            'Unverified Users'       => 'Handle unverified users',
            'KYC Unverified'         => 'Process KYC verification',
            'All Merchants'          => 'Manage merchants',
            'Pending Merchants'      => 'Review pending merchants',
            'Approved Merchants'     => 'View approved merchants',
            'Rejected Merchants'     => 'Handle rejected merchants',
            'Awaiting KYC'           => 'Process KYC requests',
            'KYC List'               => 'View all KYC records',
            'KYC Templates'          => 'Manage KYC templates',
            'Notify To Users'        => 'Send notifications',
            'All Notifications'      => 'View notifications',
            'Notifications Template' => 'Manage notification templates',
            'Currency Manage'        => 'Configure currencies',
            'Payment Gateways'       => 'Manage payment methods',
            'Virtual Card List'      => 'View virtual cards',
            'CardHolders'            => 'Manage cardholders',
            'Fee Settings'           => 'Configure fees',
            'Provider Configuration' => 'Setup card providers',
            'Deposit History'        => 'View deposits',
            'Manual Requests'        => 'Handle manual requests',
            'Automatic Methods'      => 'Configure auto methods',
            'Manual Methods'         => 'Setup manual methods',
            'Withdraws History'      => 'View withdrawals',
            'Scheduled Withdraws'    => 'Manage scheduled withdrawals',
            'Transactions'           => 'View all transactions',
            'Referrals'              => 'Manage referral system',
            'User Ranking'           => 'Configure user rankings',
            'Support Ticket'         => 'Manage support tickets',
            'Support Category'       => 'Organize ticket categories',
            'Site Settings'          => 'Configure application',
            'Plugins Manage'         => 'Manage plugins',
            'Language'               => 'Multi-language settings',
            'Staff'                  => 'Manage admin staff',
            'Roles & Permissions'    => 'Configure access control',
            'Custom Landing Page'    => 'Design landing pages',
            'Navigation Manage'      => 'Configure site navigation',
            'Page Manage'            => 'Manage website pages',
            'Component Manage'       => 'Handle page components',
            'Footer Manage'          => 'Configure footer',
            'Blog'                   => 'Manage blog posts',
            'Category'               => 'Organize blog categories',
            'Subscribers'            => 'Manage email subscribers',
            'Social Links'           => 'Configure social media',
            'SEO Manage'             => 'Optimize for search engines',
            'Activity Log'           => 'Monitor user activities',
            'Style Manager'          => 'Customize application styles',
            'Optimize App'           => 'Performance optimization',
            'Clear Cache App'        => 'Clear application cache',
            'App Info'               => 'View application information',
        ];

        return $descriptions[$label] ?? 'Administrative function';
    }

    /**
     * Get feature color scheme based on label
     */
    private function getFeatureColor($label)
    {
        $colorMap = [
            'Dashboard'    => 'primary',
            'Users'        => 'info',
            'Merchants'    => 'success',
            'KYC'          => 'warning',
            'Notification' => 'secondary',
            'Currency'     => 'success',
            'Payment'      => 'primary',
            'Virtual'      => 'info',
            'Deposit'      => 'success',
            'Withdraw'     => 'danger',
            'Transaction'  => 'primary',
            'Referral'     => 'warning',
            'Support'      => 'info',
            'Setting'      => 'secondary',
            'Language'     => 'primary',
            'Staff'        => 'warning',
            'Role'         => 'danger',
            'Page'         => 'info',
            'Blog'         => 'success',
            'Social'       => 'primary',
            'SEO'          => 'warning',
            'Activity'     => 'secondary',
            'Style'        => 'info',
            'Optimize'     => 'success',
            'App'          => 'primary',
        ];

        foreach ($colorMap as $keyword => $color) {
            if (stripos($label, $keyword) !== false) {
                return $color;
            }
        }

        return 'secondary';
    }
}
