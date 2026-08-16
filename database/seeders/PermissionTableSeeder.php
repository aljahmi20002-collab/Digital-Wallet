<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionTableSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const PERMISSION_CATALOG = [
        'dashboard' => [
            'dashboard-stats',
            'transactions-chart',
            'wallet-balance',
            'earning-chart',
            'wallet-growth',
            'wallet-latest-transactions',
            'wallet-latest-users',
        ],
        'user' => [
            'user-list',
            'user-create',
            'user-manage',
            'user-delete',
            'user-activity-log',
            'user-login-as',
            'user-balance-manage',
            'user-features-manage',
            'custom-notify-users',
        ],
        'role' => [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
        ],
        'staff' => [
            'staff-list',
            'staff-create',
            'staff-edit',
        ],
        'merchant' => [
            'merchant-list',
            'merchant-manage',
            'merchant-request-notification',
        ],
        'agent' => [
            'agent-list',
            'agent-manage',
            'agent-commission-rules-manage',
            'agent-request-notification',
        ],
        'kyc' => [
            'kyc-list',
            'kyc-action',
            'kyc-notification',
            'kyc-template-list',
            'kyc-template-manage',
        ],
        'virtual-card' => [
            'virtual-card-list',
            'virtual-card-action',
            'virtual-card-notification',
            'virtual-card-provider-manage',
        ],
        'gift-card' => [
            'gift-card-list',
            'gift-card-manage',
            'gift-card-template-list',
            'gift-card-template-manage',
        ],
        'deposit' => [
            'deposit-list',
            'deposit-action',
            'deposit-method-list',
            'deposit-method-manage',
            'deposit-notification',
        ],
        'withdraw' => [
            'withdraw-list',
            'withdraw-action',
            'withdraw-method-list',
            'withdraw-method-manage',
            'withdraw-schedule',
            'withdraw-notification',
        ],
        'payment' => [
            'payment-gateway-list',
            'payment-gateway-configure',
        ],
        'subscription' => [
            'subscription-list',
            'subscription-manage',
        ],
        'wallet-earn' => [
            'wallet-earn-list',
            'wallet-earn-manage',
        ],
        'payment-link' => [
            'payment-link-list',
            'payment-link-manage',
        ],
        'mobile-recharge' => [
            'mobile-recharge-list',
            'mobile-recharge-manage',
        ],
        'background-tasks' => [
            'background-task-list',
            'background-task-run',
            'queue-manage',
        ],
        'site-settings' => [
            'site-setting-view',
            'site-setting-update',
        ],
        'language' => [
            'language-list',
            'language-create',
            'language-manage',
        ],
        'navigation' => [
            'navigation-manage',
        ],
        'page' => [
            'page-list',
            'page-create',
            'page-edit',
            'page-delete',
            'page-footer-manage',
        ],
        'custom-landing' => [
            'custom-landing-list',
            'custom-landing-manage',
        ],
        'component' => [
            'component-list',
            'component-manage',
        ],
        'theme-manager' => [
            'theme-manager-view',
            'theme-manager-update',
        ],
        'blog' => [
            'blog-list',
            'blog-create',
            'blog-edit',
            'blog-delete',
            'blog-category-list',
            'blog-category-manage',
        ],
        'subscriber' => [
            'subscriber-list',
            'subscriber-manage',
        ],
        'social' => [
            'social-list',
            'social-manage',
        ],
        'transaction' => [
            'transaction-list',
        ],
        'p2p' => [
            'p2p-manage',
            'p2p-method-manage',
            'p2p-dispute-manage',
        ],
        'ranking' => [
            'ranking-manage',
        ],
        'referral' => [
            'referral-manage',
        ],
        'notification' => [
            'notification-list',
            'notification-plugin-list',
            'notification-template-list',
            'notification-template-manage',
        ],
        'support' => [
            'support-ticket-list',
            'support-ticket-category-manage',
            'support-ticket-manage',
            'support-ticket-notification',
        ],
        'seo' => [
            'seo-manage',
        ],
        'currency' => [
            'currency-manage',
        ],
        'plugins' => [
            'plugins-manage',
        ],
        'feature' => [
            'feature-list',
            'feature-manage',
        ],
        'app' => [
            'app-info',
            'style-manager',
            'app-clear-cache',
            'app-optimize',
            'project-updater-view',
            'project-updater-manage',
        ],
    ];

    /**
     * @var array<string, array{label: string, icon: string, summary: string, description: string}>
     */
    private const CATEGORY_METADATA = [
        'dashboard'        => ['label' => 'Dashboard', 'icon' => 'cil-speedometer', 'summary' => 'Stats, charts, wallets', 'description' => 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.'],
        'user'             => ['label' => 'User Management', 'icon' => 'users-1', 'summary' => 'Users, balances, access', 'description' => 'Customer records, balances, activity logs, login access, and feature overrides.'],
        'role'             => ['label' => 'Roles & Permissions', 'icon' => 'role', 'summary' => 'Access roles, policies', 'description' => 'Role creation, editing, deletion, and permission assignment controls.'],
        'staff'            => ['label' => 'Staff Management', 'icon' => 'badge-account', 'summary' => 'Admins, staff profiles', 'description' => 'Internal admin accounts, staff visibility, and profile management controls.'],
        'merchant'         => ['label' => 'Merchant Management', 'icon' => 'merchant', 'summary' => 'Merchants, requests', 'description' => 'Merchant onboarding, review, profile management, and request notifications.'],
        'agent'            => ['label' => 'Agent Management', 'icon' => 'agent', 'summary' => 'Agents, requests', 'description' => 'Agent onboarding, review, profile management, and request notifications.'],
        'kyc'              => ['label' => 'KYC Management', 'icon' => 'kyc', 'summary' => 'KYC review, templates', 'description' => 'Identity verification queues, approval actions, templates, and compliance notices.'],
        'virtual-card'     => ['label' => 'Virtual Cards', 'icon' => 'virtual-card', 'summary' => 'Cards, requests, providers', 'description' => 'Virtual card requests, card actions, provider settings, and admin notifications.'],
        'gift-card'        => ['label' => 'Gift Cards', 'icon' => 'tags', 'summary' => 'Cards, templates, designs', 'description' => 'Issued gift cards, cancellations, template catalog, design presets, and template reordering.'],
        'deposit'          => ['label' => 'Deposits', 'icon' => 'wallet-plus', 'summary' => 'Requests, methods', 'description' => 'Deposit requests, payment methods, operational actions, and admin notifications.'],
        'withdraw'         => ['label' => 'Withdrawals', 'icon' => 'withdraw-1', 'summary' => 'Requests, schedules', 'description' => 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.'],
        'payment'          => ['label' => 'Payment Gateways', 'icon' => 'payment', 'summary' => 'Gateways, credentials', 'description' => 'Payment provider listing, gateway credentials, and provider configuration.'],
        'subscription'     => ['label' => 'Subscriptions', 'icon' => 'layer', 'summary' => 'Plans, subscribers', 'description' => 'Subscription plans, user subscriptions, and subscription lifecycle operations.'],
        'wallet-earn'      => ['label' => 'Wallet Earn', 'icon' => 'trending-up', 'summary' => 'Earn plans, stakes', 'description' => 'Earn plans, staking activity, and wallet earning program management.'],
        'payment-link'     => ['label' => 'Payment Links', 'icon' => 'payment', 'summary' => 'Links, status, review', 'description' => 'Payment link listing, review, status changes, and cleanup controls.'],
        'mobile-recharge'  => ['label' => 'Mobile Recharge', 'icon' => 'mobile-recharge', 'summary' => 'Top-ups, providers, history', 'description' => 'Mobile recharge history, provider configuration, fees, limits, and operational settings.'],
        'background-tasks' => ['label' => 'Background Tasks', 'icon' => 'apps-1', 'summary' => 'Tasks, queues, jobs', 'description' => 'Background task visibility, manual task runs, and queue operations.'],
        'site-settings'    => ['label' => 'Site Settings', 'icon' => 'site-setting', 'summary' => 'Brand, security, system', 'description' => 'Platform-wide settings, brand controls, security options, and system preferences.'],
        'language'         => ['label' => 'Languages', 'icon' => 'translate', 'summary' => 'Locales, translations', 'description' => 'Language availability, translation creation, and localization management.'],
        'navigation'       => ['label' => 'Navigation', 'icon' => 'list-2', 'summary' => 'Menus, public links', 'description' => 'Public navigation structure, menus, labels, and link organization.'],
        'page'             => ['label' => 'Pages', 'icon' => 'page', 'summary' => 'Pages, footer CMS', 'description' => 'CMS pages, footer content, page creation, editing, and deletion controls.'],
        'custom-landing'   => ['label' => 'Custom Landing Pages', 'icon' => 'custom-landing', 'summary' => 'Campaign pages, publishing', 'description' => 'Secure custom landing uploads, previews, publishing, and HTML editing controls.'],
        'component'        => ['label' => 'Page Components', 'icon' => 'layer', 'summary' => 'Reusable content blocks', 'description' => 'Reusable page sections, content blocks, and component management.'],
        'theme-manager'    => ['label' => 'Theme Manager', 'icon' => 'quick-style', 'summary' => 'Site theme switcher', 'description' => 'Pick the active visual theme (Classic / Golden) that drives the public landing page and the builder component library.'],
        'blog'             => ['label' => 'Blog & Categories', 'icon' => 'blog', 'summary' => 'Posts, categories', 'description' => 'Blog posts, editorial categories, publishing, editing, and cleanup.'],
        'subscriber'       => ['label' => 'Subscribers', 'icon' => 'email', 'summary' => 'Audience, newsletter', 'description' => 'Subscriber records, newsletter audience visibility, and communication controls.'],
        'social'           => ['label' => 'Social Links', 'icon' => 'social-link', 'summary' => 'Profiles, public links', 'description' => 'Social profile links, public channel visibility, and link management.'],
        'transaction'      => ['label' => 'Transactions', 'icon' => 'transaction-2', 'summary' => 'Ledger, activity', 'description' => 'Transaction history, ledger visibility, and financial activity review.'],
        'p2p'              => ['label' => 'P2P Trading', 'icon' => 'p2p_trading', 'summary' => 'Marketplace, disputes', 'description' => 'P2P marketplace settings, payment methods, dispute handling, and promotions.'],
        'ranking'          => ['label' => 'User Ranking', 'icon' => 'ranking', 'summary' => 'Ranks, loyalty tiers', 'description' => 'User ranking rules, loyalty tiers, and progression controls.'],
        'referral'         => ['label' => 'Referral Program', 'icon' => 'referral', 'summary' => 'Rewards, referrals', 'description' => 'Referral rewards, program settings, and referral activity management.'],
        'notification'     => ['label' => 'Notifications', 'icon' => 'notification', 'summary' => 'Templates, plugins', 'description' => 'Notification records, channel plugins, templates, and message configuration.'],
        'support'          => ['label' => 'Support Center', 'icon' => 'support', 'summary' => 'Tickets, replies', 'description' => 'Support tickets, categories, replies, assignment flow, and ticket notifications.'],
        'seo'              => ['label' => 'SEO Management', 'icon' => 'seo', 'summary' => 'Metadata, search', 'description' => 'SEO metadata, page search presentation, and discoverability settings.'],
        'currency'         => ['label' => 'Currency Management', 'icon' => 'money-cog', 'summary' => 'Currencies, fees', 'description' => 'Currency availability, exchange settings, role limits, and fee controls.'],
        'plugins'          => ['label' => 'Plugins', 'icon' => 'cil-fork', 'summary' => 'Integrations, add-ons', 'description' => 'Installed plugins, integration controls, and add-on management.'],
        'feature'          => ['label' => 'Feature Management', 'icon' => 'feature-management', 'summary' => 'Toggles, rules', 'description' => 'Feature switches, rollout rules, and panel access controls.'],
        'app'              => ['label' => 'Application Tools', 'icon' => 'app', 'summary' => 'Cache, info, style', 'description' => 'Application info, cache actions, optimization tools, and style manager access.'],
    ];

    /**
     * @var array<string, array{description: string, permissions: list<string>}>
     */
    private const ROLE_PRESETS = [
        'finance-manager' => [
            'description' => 'Controls deposits, withdrawals, gateways, wallet balances, and financial reporting.',
            'permissions' => [
                'dashboard-stats',
                'transactions-chart',
                'wallet-balance',
                'deposit-list',
                'deposit-action',
                'deposit-method-list',
                'withdraw-list',
                'withdraw-action',
                'withdraw-method-list',
                'withdraw-schedule',
                'payment-gateway-list',
                'payment-gateway-configure',
                'subscription-list',
                'wallet-earn-list',
                'payment-link-list',
                'mobile-recharge-list',
                'mobile-recharge-manage',
                'transaction-list',
                'currency-manage',
                'gift-card-list',
            ],
        ],
        'support-executive' => [
            'description' => 'Handles customer profiles, tickets, notifications, KYC visibility, and transaction lookup.',
            'permissions' => [
                'dashboard-stats',
                'user-list',
                'user-manage',
                'user-activity-log',
                'kyc-list',
                'transaction-list',
                'support-ticket-list',
                'support-ticket-manage',
                'support-ticket-category-manage',
                'support-ticket-notification',
                'custom-notify-users',
                'notification-list',
            ],
        ],
        'kyc-officer' => [
            'description' => 'Reviews identity submissions, manages KYC actions, templates, and compliance notices.',
            'permissions' => [
                'dashboard-stats',
                'user-list',
                'merchant-list',
                'agent-list',
                'kyc-list',
                'kyc-action',
                'kyc-notification',
                'kyc-template-list',
                'kyc-template-manage',
                'notification-template-list',
            ],
        ],
        'content-manager' => [
            'description' => 'Maintains public pages, blog content, navigation, subscribers, social links, and SEO.',
            'permissions' => [
                'page-list',
                'page-create',
                'page-edit',
                'page-footer-manage',
                'custom-landing-list',
                'custom-landing-manage',
                'component-list',
                'component-manage',
                'theme-manager-view',
                'theme-manager-update',
                'blog-list',
                'blog-create',
                'blog-edit',
                'blog-category-list',
                'blog-category-manage',
                'navigation-manage',
                'subscriber-list',
                'subscriber-manage',
                'social-list',
                'social-manage',
                'seo-manage',
                'gift-card-template-list',
                'gift-card-template-manage',
            ],
        ],
        'operations-manager' => [
            'description' => 'Oversees users, merchants, agents, finance queues, transactions, features, and app health.',
            'permissions' => [
                'dashboard-stats',
                'transactions-chart',
                'wallet-balance',
                'wallet-latest-transactions',
                'wallet-latest-users',
                'user-list',
                'user-manage',
                'merchant-list',
                'merchant-manage',
                'agent-list',
                'agent-manage',
                'agent-commission-rules-manage',
                'deposit-list',
                'deposit-action',
                'withdraw-list',
                'withdraw-action',
                'transaction-list',
                'background-task-list',
                'feature-list',
                'feature-manage',
                'app-info',
                'app-clear-cache',
                'gift-card-list',
                'gift-card-manage',
                'gift-card-template-list',
            ],
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionColumns = $this->permissionColumns();
        $permissionRows    = $this->permissionRows()
            ->map(fn (array $row): array => collect($row)->only($permissionColumns)->all());
        $updatableColumns = collect([
            'category',
            'display_name',
            'description',
            'category_display_name',
            'category_icon',
            'category_summary',
            'category_description',
            'updated_at',
        ])
            ->filter(fn (string $column): bool => in_array($column, $permissionColumns, true))
            ->values()
            ->all();

        Permission::query()->upsert(
            $permissionRows->all(),
            ['name', 'guard_name'],
            $updatableColumns
        );

        $superRole = Role::query()->firstOrCreate(
            ['guard_name' => 'admin', 'name' => 'super-admin'],
            ['description' => 'Full platform access with every permission.']
        );

        if (blank($superRole->description)) {
            $superRole->forceFill([
                'description' => 'Full platform access with every permission.',
            ])->save();
        }

        $superRole->syncPermissions(Permission::query()->where('guard_name', 'admin')->get());

        $this->syncPresetRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return Collection<int, array{name: string, display_name: string, description: string, category: string, category_display_name: string, category_icon: string, category_summary: string, category_description: string, guard_name: string, created_at: Carbon, updated_at: Carbon}>
     */
    private function permissionRows(): Collection
    {
        $now = now();

        return collect(self::PERMISSION_CATALOG)
            ->flatMap(fn (array $permissions, string $category): Collection => collect($permissions)
                ->map(function (string $permission) use ($category, $now): array {
                    $categoryMetadata = self::CATEGORY_METADATA[$category] ?? [
                        'label'       => $this->categoryDisplayName($category),
                        'icon'        => $category,
                        'summary'     => 'Module controls',
                        'description' => 'Operational permissions for this module.',
                    ];

                    return [
                        'name'                  => $permission,
                        'display_name'          => $this->permissionDisplayName($permission),
                        'description'           => $this->permissionDescription($permission),
                        'category'              => $category,
                        'category_display_name' => $categoryMetadata['label'],
                        'category_icon'         => $categoryMetadata['icon'],
                        'category_summary'      => $categoryMetadata['summary'],
                        'category_description'  => $categoryMetadata['description'],
                        'guard_name'            => 'admin',
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ];
                }))
            ->values();
    }

    /**
     * @return list<string>
     */
    private function permissionColumns(): array
    {
        $table = config('permission.table_names.permissions', 'permissions');

        return collect([
            'name',
            'category',
            'display_name',
            'description',
            'category_display_name',
            'category_icon',
            'category_summary',
            'category_description',
            'guard_name',
            'created_at',
            'updated_at',
        ])
            ->filter(fn (string $column): bool => Schema::hasColumn($table, $column))
            ->values()
            ->all();
    }

    private function categoryDisplayName(string $category): string
    {
        return Str::of($category)
            ->replace('-', ' ')
            ->title()
            ->replace('Kyc', 'KYC')
            ->replace('P2P', 'P2P')
            ->replace('Seo', 'SEO')
            ->replace('App', 'Application')
            ->toString();
    }

    private function permissionDisplayName(string $permission): string
    {
        return Str::of($permission)
            ->replace('-', ' ')
            ->title()
            ->replace('Kyc', 'KYC')
            ->replace('P2P', 'P2P')
            ->replace('Seo', 'SEO')
            ->replace('App', 'Application')
            ->toString();
    }

    private function permissionDescription(string $permission): string
    {
        $label  = $this->permissionDisplayName($permission);
        $action = Str::afterLast($permission, '-');

        $overrides = [
            'dashboard-stats'                => 'View high-level platform metrics and operational dashboard cards.',
            'transactions-chart'             => 'View transaction charts and finance activity trends.',
            'wallet-balance'                 => 'View wallet balance summaries and money movement totals.',
            'earning-chart'                  => 'View earning charts and revenue performance widgets.',
            'wallet-growth'                  => 'View wallet growth trends and adoption metrics.',
            'wallet-latest-transactions'     => 'View the latest wallet transactions on the dashboard.',
            'wallet-latest-users'            => 'View newly joined users from dashboard widgets.',
            'user-login-as'                  => 'Sign in as a user for support and account troubleshooting.',
            'user-balance-manage'            => 'Adjust user balances through approved admin workflows.',
            'user-features-manage'           => 'Enable or disable feature access for individual users.',
            'custom-notify-users'            => 'Send targeted custom notifications to selected users.',
            'kyc-action'                     => 'Approve, reject, or review submitted KYC verification requests.',
            'kyc-template-manage'            => 'Create and update KYC form templates and required fields.',
            'virtual-card-provider-manage'   => 'Configure virtual card providers and card issuing rules.',
            'gift-card-list'                 => 'View all issued gift cards, recipients, statuses, and redemption history.',
            'gift-card-manage'               => 'Cancel pending, scheduled, or delivered gift cards and review redemption activity.',
            'gift-card-template-list'        => 'View the gift card template catalog, design presets, and usage stats.',
            'gift-card-template-manage'      => 'Create, edit, delete, reorder, and toggle gift card design templates.',
            'deposit-method-manage'          => 'Create and update deposit methods, limits, and availability.',
            'withdraw-method-manage'         => 'Create and update withdrawal methods, limits, and availability.',
            'withdraw-schedule'              => 'Manage scheduled withdrawal windows and payout timing.',
            'payment-gateway-configure'      => 'Update payment gateway credentials and provider settings.',
            'background-task-run'            => 'Run approved background tasks manually from the admin panel.',
            'queue-manage'                   => 'Review and manage queue operations and background job flow.',
            'site-setting-view'              => 'View site-wide configuration, branding, security, and system settings.',
            'site-setting-update'            => 'Update site-wide configuration, branding, security, and system settings.',
            'navigation-manage'              => 'Create and organize public menus, links, and navigation groups.',
            'page-footer-manage'             => 'Manage footer content, sections, links, and public page blocks.',
            'custom-landing-list'            => 'View custom landing pages, publishing status, archive metadata, and preview links.',
            'custom-landing-manage'          => 'Upload, validate, publish, edit, and delete custom landing page bundles.',
            'p2p-method-manage'              => 'Manage P2P payment methods and marketplace payment options.',
            'p2p-dispute-manage'             => 'Review and resolve P2P trade disputes and escalation cases.',
            'notification-plugin-list'       => 'View notification integrations and delivery channel plugins.',
            'notification-template-manage'   => 'Create and update reusable notification templates.',
            'support-ticket-category-manage' => 'Create and organize ticket categories for support workflows.',
            'support-ticket-manage'          => 'Reply to, assign, update, and resolve customer support tickets.',
            'agent-commission-rules-manage'  => 'Create and update agent commission rules for cash-in, cash-out, amount ranges, currencies, and agent-specific rates.',
            'currency-manage'                => 'Manage currencies, availability, rates, fees, and finance limits.',
            'plugins-manage'                 => 'Enable, disable, and configure installed platform plugins.',
            'feature-manage'                 => 'Control feature toggles, access rules, and module availability.',
            'app-info'                       => 'View application, environment, and server health information.',
            'style-manager'                  => 'Customize backend styling and visual presentation settings.',
            'app-clear-cache'                => 'Clear application cache from the admin maintenance tools.',
            'app-optimize'                   => 'Run application optimization actions from maintenance tools.',
            'project-updater-view'           => 'View project license status, update checks, changelog, and update history.',
            'project-updater-manage'         => 'Activate project licenses and install verified project update packages.',
        ];

        if (isset($overrides[$permission])) {
            return $overrides[$permission];
        }

        return match ($action) {
            'list'   => "View {$label} records, tables, and module indexes.",
            'create' => "Create new {$label} records through admin workflows.",
            'edit', 'update' => "Update {$label} records and save approved changes.",
            'delete'       => "Delete {$label} records when business rules allow it.",
            'manage'       => "Manage {$label} settings, records, and operational controls.",
            'action'       => "Run review, approval, rejection, or operational actions for {$label}.",
            'notification' => "Receive or manage admin notifications for {$label}.",
            'configure'    => "Configure {$label} credentials, settings, and provider options.",
            'run'          => "Run {$label} actions manually from the admin panel.",
            'optimize'     => "Run {$label} optimization and maintenance actions.",
            default        => "Access the {$label} admin capability.",
        };
    }

    private function syncPresetRoles(): void
    {
        foreach (self::ROLE_PRESETS as $roleName => $preset) {
            $role = Role::query()->updateOrCreate(
                ['guard_name' => 'admin', 'name' => $roleName],
                ['description' => $preset['description']]
            );

            $role->syncPermissions(
                Permission::query()
                    ->where('guard_name', 'admin')
                    ->whereIn('name', $preset['permissions'])
                    ->get()
            );
        }
    }
}
