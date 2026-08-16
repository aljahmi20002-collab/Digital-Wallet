<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Items
    |--------------------------------------------------------------------------
    |
    | Here you can change the sidebar menu items
    |
    */

    'sidebar_icons' => [
        'default'         => 'sidebar-subitem',
        'menu_size'       => 22,
        'submenu_size'    => 19,
        'quick_link_size' => 19,
        'map'             => [
            'app'                 => 'sidebar-app',
            'apps'                => 'sidebar-apps',
            'apps-1'              => 'sidebar-system',
            'auto-payment'        => 'sidebar-auto-payment',
            'badge-account'       => 'sidebar-identity',
            'blog'                => 'sidebar-blog',
            'cache'               => 'sidebar-cache',
            'card'                => 'sidebar-card',
            'cardHolder'          => 'sidebar-cardholder',
            'chart-up'            => 'sidebar-growth',
            'chat-1'              => 'sidebar-ticket-open',
            'chat-2'              => 'sidebar-ticket-progress',
            'chat-3'              => 'sidebar-ticket-closed',
            'chat-4'              => 'sidebar-ticket-history',
            'cil-fork'            => 'sidebar-plugin',
            'cil-settings'        => 'sidebar-settings',
            'cil-speedometer'     => 'sidebar-dashboard',
            'currency-exchange'   => 'sidebar-exchange',
            'custom-landing'      => 'sidebar-landing',
            'deposit'             => 'sidebar-deposit',
            'deposit-1'           => 'sidebar-history',
            'email'               => 'sidebar-email',
            'fee'                 => 'sidebar-fee',
            'feature-management'  => 'sidebar-feature',
            'footer'              => 'sidebar-footer',
            'history'             => 'sidebar-history',
            'kyc'                 => 'sidebar-kyc',
            'layer'               => 'sidebar-subscription',
            'list-2'              => 'sidebar-navigation',
            'manual-payment'      => 'sidebar-manual-payment',
            'merchant'            => 'sidebar-merchant',
            'mobile-recharge'     => 'mobile-recharge',
            'agent'               => 'sidebar-agent',
            'message'             => 'sidebar-message',
            'money-cog'           => 'sidebar-currency',
            'notification'        => 'sidebar-notification',
            'optimize'            => 'sidebar-rocket',
            'p2p_trading'         => 'sidebar-p2p',
            'page'                => 'sidebar-page',
            'palette'             => 'sidebar-palette',
            'payment'             => 'sidebar-payment',
            'people'              => 'sidebar-people',
            'provider'            => 'sidebar-provider',
            'quick-activity'      => 'history',
            'quick-settings'      => 'sidebar-settings',
            'quick-support'       => 'support-open',
            'quick-site-optimize' => 'sidebar-rocket',
            'project-updater'     => 'sidebar-system',
            'ranking'             => 'sidebar-ranking',
            'referral'            => 'sidebar-referral',
            'request-1'           => 'sidebar-request',
            'request-list'        => 'sidebar-list',
            'role'                => 'sidebar-role',
            'schedule'            => 'sidebar-schedule',
            'send-mail'           => 'sidebar-send-mail',
            'seo'                 => 'sidebar-seo',
            'shop'                => 'sidebar-store',
            'shop-1'              => 'sidebar-store',
            'site-setting'        => 'sidebar-site-setting',
            'social-link'         => 'sidebar-social',
            'support'             => 'sidebar-support',
            'tags'                => 'sidebar-tags',
            'template'            => 'sidebar-template',
            'transaction-2'       => 'sidebar-transactions',
            'translate'           => 'sidebar-language',
            'trending-up'         => 'sidebar-growth',
            'users-1'             => 'sidebar-users',
            'virtual-card'        => 'sidebar-card',
            'wallet'              => 'sidebar-wallet',
            'wallet-plus'         => 'sidebar-wallet-plus',
            'withdraw-1'          => 'sidebar-withdraw',
            'withdraw-2'          => 'sidebar-history',
        ],
    ],

    [
        'menus' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'cil-speedometer',
                'type'  => 'single',
                'route' => 'admin.dashboard',
            ],
        ],
    ],
    [
        'label' => 'Account Management',
        'menus' => [
            [
                'label'     => 'User Accounts',
                'icon'      => 'users-1',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'All Users',
                        'route'      => 'admin.user.index',
                        'permission' => 'user-list',
                    ],
                    [
                        'label'      => 'Active Users',
                        'route'      => 'admin.user.active',
                        'permission' => 'user-list',
                    ],
                    [
                        'label'      => 'Suspended Users',
                        'route'      => 'admin.user.suspended',
                        'permission' => 'user-list',
                    ],
                    [
                        'label'      => 'Unverified Users',
                        'route'      => 'admin.user.unverified',
                        'permission' => 'user-list',
                    ],
                    [
                        'label'      => 'KYC Pending Users',
                        'route'      => 'admin.user.kyc-unverified',
                        'permission' => 'user-list',
                    ],

                ],
            ],
            [
                'label'     => 'Business Profiles',
                'code'      => 'merchant-management',
                'icon'      => 'merchant',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Merchants',
                        'route'      => 'admin.merchant.index',
                        'permission' => 'merchant-list',
                        'icon'       => 'shop-1',
                    ],
                    [
                        'label'      => 'Pending Merchants',
                        'route'      => 'admin.merchant.pending',
                        'permission' => 'merchant-list',
                        'icon'       => 'shop',
                    ],
                    [
                        'label'      => 'Agents',
                        'route'      => 'admin.agent.index',
                        'permission' => 'agent-list',
                        'icon'       => 'agent',
                    ],
                    [
                        'label'      => 'Pending Agents',
                        'route'      => 'admin.agent.pending',
                        'permission' => 'agent-list',
                        'icon'       => 'agent',
                    ],
                    [
                        'label'      => 'Commission Rules',
                        'route'      => 'admin.agent.commission-rules.index',
                        'permission' => 'agent-commission-rules-manage',
                        'icon'       => 'fee',
                    ],
                ],
            ],
            [
                'label'     => 'KYC Verification',
                'icon'      => 'kyc',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Pending Reviews',
                        'route'      => 'admin.kyc.pending',
                        'permission' => 'kyc-list',
                    ],
                    [
                        'label'      => 'Verification Records',
                        'route'      => 'admin.kyc.index',
                        'permission' => 'kyc-list',
                    ],
                    [
                        'label'      => 'KYC Templates',
                        'route'      => 'admin.kyc.template.index',
                        'permission' => 'kyc-template-list',
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'Communication Center',
        'menus' => [
            [
                'label'     => 'Notifications',
                'icon'      => 'notification',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Send Notification',
                        'icon'       => 'send-mail',
                        'route'      => 'admin.notifications.notifyToUser',
                        'permission' => 'custom-notify-users',
                    ],
                    [
                        'label'      => 'Notification Logs',
                        'icon'       => 'notification',
                        'route'      => 'admin.notifications.index',
                        'permission' => 'notification-list',
                    ],
                    [
                        'label'      => 'Notification Templates',
                        'icon'       => 'template',
                        'route'      => 'admin.notifications.template.index',
                        'permission' => 'notification-template-list',
                    ],
                ],
            ],
            [
                'label'      => 'Email Subscribers',
                'icon'       => 'email',
                'type'       => 'single',
                'route'      => 'admin.subscriber.index',
                'permission' => 'subscriber-list',
            ],
            [
                'label'     => 'Support Tickets',
                'icon'      => 'message',
                'code'      => 'support-ticket',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'New Tickets',
                        'icon'       => 'chat-1',
                        'route'      => 'admin.support-ticket.new',
                        'permission' => 'support-ticket-list',
                    ],
                    [
                        'label'      => 'In Progress',
                        'icon'       => 'chat-2',
                        'route'      => 'admin.support-ticket.inprogress',
                        'permission' => 'support-ticket-list',
                    ],
                    [
                        'label'      => 'Closed Tickets',
                        'icon'       => 'chat-3',
                        'route'      => 'admin.support-ticket.close',
                        'permission' => 'support-ticket-list',
                    ],
                    [
                        'label'      => 'Ticket History',
                        'icon'       => 'chat-4',
                        'route'      => 'admin.support-ticket.history',
                        'permission' => 'support-ticket-list',
                    ],
                    [
                        'label'      => 'Ticket Categories',
                        'icon'       => 'tags',
                        'route'      => 'admin.support-ticket.category.index',
                        'permission' => 'support-ticket-category-manage',
                    ],
                ],
            ],
        ],
    ],

    [
        'label' => 'Finance & Wallet',
        'menus' => [
            [
                'label'      => 'Currency Management',
                'icon'       => 'money-cog',
                'type'       => 'single',
                'route'      => 'admin.currency.index',
                'permission' => 'currency-manage',
            ],
            [
                'label'      => 'Payment Gateways',
                'icon'       => 'payment',
                'type'       => 'single',
                'route'      => 'admin.payment.gateway.index',
                'permission' => 'payment-gateway-list',
            ],
            [
                'label'       => 'Payment Links',
                'code'        => 'payment-link-management',
                'icon'        => 'payment',
                'type'        => 'single',
                'route'       => 'admin.payment-links.index',
                'permission'  => 'payment-link-list',
                'feature_key' => 'payment_link',
            ],
            [
                'label'       => 'Mobile Recharge',
                'code'        => 'mobile-recharge-management',
                'icon'        => 'mobile-recharge',
                'type'        => 'single',
                'route'       => 'admin.mobile-recharge.index',
                'permission'  => 'mobile-recharge-list',
                'feature_key' => 'mobile_recharge',
            ],
            [
                'label'       => 'P2P Marketplace',
                'code'        => 'p2p-management',
                'icon'        => 'p2p_trading',
                'type'        => 'groups',
                'permission'  => 'p2p-manage',
                'feature_key' => 'p2p_marketplace',
                'sub_menus'   => [
                    [
                        'label'      => 'P2P Dashboard',
                        'icon'       => 'transaction-2',
                        'route'      => 'admin.p2p.index',
                        'permission' => 'p2p-manage',
                    ],
                    [
                        'label'      => 'Settings',
                        'icon'       => 'cil-settings',
                        'route'      => 'admin.p2p.settings.edit',
                        'permission' => 'p2p-manage',
                    ],
                    [
                        'label'      => 'Payment Methods',
                        'icon'       => 'payment',
                        'route'      => 'admin.p2p.methods.index',
                        'permission' => 'p2p-method-manage',
                    ],
                    [
                        'label'      => 'Traders',
                        'icon'       => 'badge-account',
                        'route'      => 'admin.p2p.advertisers.index',
                        'permission' => 'p2p-manage',
                    ],
                    [
                        'label'      => 'Disputes',
                        'icon'       => 'support',
                        'route'      => 'admin.p2p.disputes.index',
                        'permission' => 'p2p-dispute-manage',
                    ],
                    [
                        'label'      => 'Promotions',
                        'icon'       => 'chart-up',
                        'route'      => 'admin.p2p.promotions.index',
                        'permission' => 'p2p-manage',
                    ],
                ],
            ],
            [
                'label'      => 'Subscriptions',
                'code'       => 'subscription-management',
                'icon'       => 'layer',
                'type'       => 'groups',
                'permission' => 'subscription-list',
                'sub_menus'  => [
                    [
                        'label'      => 'Plans',
                        'icon'       => 'apps',
                        'route'      => 'admin.subscription.plans.index',
                        'permission' => 'subscription-list',
                    ],
                    [
                        'label'      => 'User Plans',
                        'icon'       => 'people',
                        'route'      => 'admin.subscription.user-subscriptions.index',
                        'permission' => 'subscription-list',
                    ],
                    [
                        'label'      => 'Transactions',
                        'icon'       => 'transaction-2',
                        'route'      => 'admin.subscription.transactions',
                        'permission' => 'subscription-list',
                    ],
                ],
            ],
            [
                'label'       => 'Wallet Earn',
                'code'        => 'wallet-earn-management',
                'icon'        => 'trending-up',
                'type'        => 'groups',
                'permission'  => 'wallet-earn-list',
                'feature_key' => 'wallet_earn',
                'sub_menus'   => [
                    [
                        'label'      => 'Earn Dashboard',
                        'icon'       => 'wallet',
                        'route'      => 'admin.wallet-earn.index',
                        'permission' => 'wallet-earn-list',
                    ],
                    [
                        'label'      => 'Plans',
                        'icon'       => 'apps',
                        'route'      => 'admin.wallet-earn.plans.index',
                        'permission' => 'wallet-earn-list',
                    ],
                ],
            ],
            [
                /*
                 * Icon keys must match an entry in `sidebar_icons.map`
                 * above (lines 19-92). Unmapped keys silently fall back
                 * to `sidebar-subitem` — that's what produced the broken
                 * arrow on the previous `gift` / `card-approved` values
                 * (and earlier on `voucher` / `list-2` / `apps-1`). All
                 * three keys below resolve to real sidebar-*.svg files
                 * in public/general/static/svg/.
                 */
                'label'       => 'Gift Cards',
                'code'        => 'gift-card-management',
                'icon'        => 'tags',           // → sidebar-tags (gift codes feel tag-like)
                'type'        => 'groups',
                'feature_key' => 'gift_cards',
                'permission'  => 'gift-card-list',
                'sub_menus'   => [
                    [
                        'label'      => 'All Gift Cards',
                        'icon'       => 'request-list', // → sidebar-list (clean list glyph)
                        'route'      => 'admin.gift-cards.index',
                        'permission' => 'gift-card-list',
                    ],
                    [
                        'label'      => 'Templates',
                        'icon'       => 'palette',      // → sidebar-palette (already mapped)
                        'route'      => 'admin.gift-card-templates.index',
                        'permission' => 'gift-card-template-list',
                    ],
                ],
            ],
            [
                'label'      => 'Virtual Cards',
                'code'       => 'virtual-card-management',
                'icon'       => 'virtual-card',
                'type'       => 'groups',
                'permission' => 'virtual-card-list',
                'sub_menus'  => [
                    [
                        'label'      => 'Awaiting Requests',
                        'icon'       => 'request-1',
                        'route'      => 'admin.virtual-card.requests.awaiting',
                        'permission' => 'virtual-card-action',
                    ],
                    [
                        'label'      => 'Cardholders',
                        'icon'       => 'cardHolder',
                        'route'      => 'admin.virtual-card.cardholders.index',
                        'permission' => 'virtual-card-action',
                    ],
                    [
                        'label'      => 'All Requests',
                        'icon'       => 'request-list',
                        'route'      => 'admin.virtual-card.requests.all',
                        'permission' => 'virtual-card-list',
                    ],
                    [
                        'label'      => 'All Cards',
                        'icon'       => 'card',
                        'route'      => 'admin.virtual-card.list',
                        'permission' => 'virtual-card-list',
                    ],
                    [
                        'label'      => 'Fee Settings',
                        'icon'       => 'fee',
                        'route'      => 'admin.virtual-card.fee-settings.index',
                        'permission' => 'virtual-card-action',
                    ],
                    [
                        'label'      => 'Provider Settings',
                        'icon'       => 'provider',
                        'route'      => 'admin.virtual-card.provider.index',
                        'permission' => 'virtual-card-provider-manage',
                    ],
                ],
            ],
            [
                'label'       => 'Deposits',
                'code'        => 'deposit-management',
                'feature_key' => 'deposit_money',
                'icon'        => 'wallet-plus',
                'type'        => 'groups',
                'sub_menus'   => [
                    [
                        'label'      => 'Manual Requests',
                        'icon'       => 'deposit',
                        'route'      => 'admin.deposit.manual-request',
                        'permission' => 'deposit-list',
                    ],
                    [
                        'label'      => 'Automatic Methods',
                        'icon'       => 'auto-payment',
                        'route'      => 'admin.deposit.method.index',
                        'params'     => ['type' => 'auto'],
                        'permission' => 'deposit-method-list',
                    ],
                    [
                        'label'      => 'Manual Methods',
                        'icon'       => 'manual-payment',
                        'route'      => 'admin.deposit.method.index',
                        'params'     => ['type' => 'manual'],
                        'permission' => 'deposit-method-list',
                    ],
                    [
                        'label'      => 'Deposit History',
                        'icon'       => 'deposit-1',
                        'route'      => 'admin.deposit.history',
                        'permission' => 'deposit-list',
                    ],
                ],
            ],
            [
                'label'       => 'Withdrawals',
                'code'        => 'withdraw-management',
                'feature_key' => 'withdraw_money',
                'icon'        => 'withdraw-1',
                'type'        => 'groups',
                'sub_menus'   => [
                    [
                        'label'      => 'Manual Requests',
                        'icon'       => 'withdraw-1',
                        'route'      => 'admin.withdraw.manual-request',
                        'permission' => 'withdraw-list',
                    ],
                    [
                        'label'      => 'Automatic Methods',
                        'icon'       => 'auto-payment',
                        'route'      => 'admin.withdraw.method.index',
                        'params'     => ['type' => 'auto'],
                        'permission' => 'withdraw-method-list',
                    ],

                    [
                        'label'      => 'Manual Methods',
                        'icon'       => 'manual-payment',
                        'route'      => 'admin.withdraw.method.index',
                        'params'     => ['type' => 'manual'],
                        'permission' => 'withdraw-method-list',
                    ],
                    [
                        'label'      => 'Scheduled Withdrawals',
                        'icon'       => 'schedule',
                        'route'      => 'admin.withdraw.schedule',
                        'permission' => 'withdraw-schedule',
                    ],
                    [
                        'label'      => 'Withdrawal History',
                        'icon'       => 'withdraw-2',
                        'route'      => 'admin.withdraw.history',
                        'permission' => 'withdraw-list',
                    ],
                ],
            ],
            [
                'label'      => 'Referral Program',
                'icon'       => 'referral',
                'type'       => 'single',
                'route'      => 'admin.referral.index',
                'permission' => 'referral-manage',
            ],
            [
                'label'      => 'Transactions',
                'icon'       => 'transaction-2',
                'type'       => 'single',
                'route'      => 'admin.transaction',
                'permission' => 'transaction-list',
            ],
        ],
    ],
    [
        'label' => 'System Config',
        'menus' => [
            [
                'label'     => 'Settings',
                'code'      => 'settings-management',
                'icon'      => 'cil-settings',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Site Settings',
                        'icon'       => 'site-setting',
                        'route'      => 'admin.settings.site.index',
                        'permission' => 'site-setting-view',
                    ],
                    [
                        'label'      => 'Integration Center',
                        'icon'       => 'cil-fork',
                        'route'      => 'admin.settings.plugin.index',
                        'permission' => 'plugins-manage',
                    ],
                    [
                        'label'                 => 'Feature Controls',
                        'icon'                  => 'feature-management',
                        'route'                 => 'admin.features.index',
                        'permission'            => 'feature-list',
                        'show_in_settings_tabs' => false,
                    ],
                    [
                        'label'                 => 'User Rankings',
                        'icon'                  => 'ranking',
                        'route'                 => 'admin.ranking.index',
                        'permission'            => 'ranking-manage',
                        'feature_key'           => 'user_ranks',
                        'show_in_settings_tabs' => false,
                    ],
                    [
                        'label'                 => 'Languages',
                        'icon'                  => 'translate',
                        'route'                 => 'admin.language.index',
                        'permission'            => 'language-list',
                        'show_in_settings_tabs' => false,
                    ],
                    [
                        'label'                 => 'Background Jobs',
                        'icon'                  => 'apps-1',
                        'route'                 => 'admin.background-tasks.index',
                        'permission'            => 'background-task-list',
                        'show_in_settings_tabs' => false,
                    ],
                    [
                        'label'                 => 'Project Updater',
                        'icon'                  => 'project-updater',
                        'route'                 => 'admin.app.updater.index',
                        'permission'            => 'project-updater-view',
                        'show_in_settings_tabs' => false,
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'Staff Management',
        'menus' => [
            [
                'label'      => 'Team Members',
                'icon'       => 'badge-account',
                'type'       => 'single',
                'route'      => 'admin.staff.index',
                'permission' => 'staff-list',
            ],
            [
                'label'      => 'Roles & Permissions',
                'icon'       => 'role',
                'type'       => 'single',
                'route'      => 'admin.role.index',
                'permission' => 'role-list',
            ],
        ],
    ],
    [
        'label' => 'Content Management',
        'menus' => [
            [
                'label'     => 'Site Builder',
                'icon'      => 'page',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Theme Manager',
                        'icon'       => 'quick-style',
                        'route'      => 'admin.theme-manager.index',
                        'permission' => 'theme-manager-view',
                    ],
                    [
                        'label'      => 'Landing Page',
                        'icon'       => 'custom-landing',
                        'route'      => 'admin.custom-landing.index',
                        'permission' => 'custom-landing-list',
                    ],
                    [
                        'label'      => 'All Pages',
                        'icon'       => 'page',
                        'route'      => 'admin.page.site.index',
                        'permission' => 'page-list',
                    ],
                    [
                        'label'      => 'Page Components',
                        'icon'       => 'template',
                        'route'      => 'admin.page.component.index',
                        'permission' => 'component-list',
                    ],
                    [
                        'label'      => 'Site Navigation',
                        'icon'       => 'list-2',
                        'route'      => 'admin.navigation.site.index',
                        'permission' => 'navigation-manage',
                    ],
                    [
                        'label'      => 'Style Manager',
                        'icon'       => 'quick-style',
                        'route'      => 'admin.app.style-manager',
                        'permission' => 'style-manager',
                    ],
                    [
                        'label'      => 'Footer Sections',
                        'icon'       => 'footer',
                        'route'      => 'admin.page.footer.section.index',
                        'permission' => 'page-footer-manage',
                    ],
                ],
            ],
            [
                'label'     => 'SEO & Social',
                'icon'      => 'seo',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'SEO Settings',
                        'icon'       => 'seo',
                        'route'      => 'admin.site-seo.index',
                        'permission' => 'seo-manage',
                    ],
                    [
                        'label'      => 'Social Links',
                        'icon'       => 'social-link',
                        'route'      => 'admin.social.index',
                        'permission' => 'social-list',
                    ],
                ],
            ],
            [
                'label'     => 'Blog',
                'icon'      => 'blog',
                'type'      => 'groups',
                'sub_menus' => [
                    [
                        'label'      => 'Blog Posts',
                        'icon'       => 'blog',
                        'route'      => 'admin.blog.post.index',
                        'permission' => 'blog-list',
                    ],
                    [
                        'label'      => 'Blog Categories',
                        'icon'       => 'tags',
                        'route'      => 'admin.blog.category.index',
                        'permission' => 'blog-category-list',
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Shortcut Links
    |--------------------------------------------------------------------------
    |
    | Priority shortcut tiles displayed at the bottom of the sidebar.
    | Arranged for frequent monitoring, settings, support, and optimize work.
    |
    */
    'sidebar_footer' => [
        [
            'label'      => 'Activity',
            'icon'       => 'quick-activity',
            'route'      => 'admin.activity-log',
            'permission' => 'user-activity-log',
            'accent'     => 'info',
        ],
        [
            'label'      => 'Settings',
            'icon'       => 'quick-settings',
            'route'      => 'admin.settings.site.index',
            'permission' => 'site-setting-view',
            'accent'     => 'primary',
        ],
        [
            'label'      => 'Tickets',
            'title'      => 'In Progress Support Tickets',
            'icon'       => 'quick-support',
            'route'      => 'admin.support-ticket.inprogress',
            'permission' => 'support-ticket-list',
            'accent'     => 'warning',
        ],
        [
            'label'      => 'Optimize',
            'title'      => 'Site Optimize',
            'icon'       => 'quick-site-optimize',
            'route'      => 'admin.app.optimize',
            'permission' => 'app-optimize',
            'accent'     => 'success',
        ],
    ],

];
