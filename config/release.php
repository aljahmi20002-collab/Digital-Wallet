<?php

/*
|--------------------------------------------------------------------------
| Release build configuration
|--------------------------------------------------------------------------
|
| Used by `php artisan release:build` to produce a clean CodeCanyon zip:
|   • everything dev-only (.git, tests, IDE configs, AI tool dirs, etc.)
|     is stripped from the file tree
|   • the SQL dump in the zip contains only system / reference data —
|     no users, admins, transactions, sessions or other tenant data
|
| Edit the lists below when you add new dev tools or new database tables.
*/

return [

    /*
    |----------------------------------------------------------------------
    | Files & folders to EXCLUDE from the release zip
    |----------------------------------------------------------------------
    |
    | Paths are matched against the project-relative path (forward slashes).
    | An entry of "node_modules" matches both top-level "node_modules" and
    | any nested "some/path/node_modules". Glob-style wildcards (e.g. "*.log")
    | are supported via fnmatch().
    */

    'exclude_paths' => [

        // ── Version control & CI ────────────────────────────────────────
        '.git',
        '.gitignore',
        '.gitattributes',
        '.github',
        '.gitlab-ci.yml',

        // ── IDEs & editors ──────────────────────────────────────────────
        '.idea',
        '.vscode',
        '.vs',
        '.fleet',
        '.phpactor.json',
        '.phpactor.yml',
        '.editorconfig',
        '_ide_helper.php',
        '_ide_helper_models.php',
        '.phpstorm.meta.php',

        // ── OS metadata ─────────────────────────────────────────────────
        '.DS_Store',
        'Thumbs.db',
        'desktop.ini',
        '*~',
        '*.bak',
        '*.tmp',

        // ── Node / front-end build cache ────────────────────────────────
        'node_modules',
        'npm-debug.log',
        'yarn-error.log',
        'yarn.lock',
        // public/build IS shipped — that's the compiled output buyers need.
        'public/hot',

        // ── Tests & dev tooling ─────────────────────────────────────────
        'tests',
        'phpunit.xml',
        'phpunit.xml.dist',
        '.phpunit.cache',
        '.phpunit.result.cache',
        'pint.json',
        'pest.config.php',

        // ── Composer dev cruft (belt-and-braces — `composer install
        //    --no-dev` already removes most of this, but the IDE helper
        //    dump and the bin/ folder hang around regardless) ──────────
        'vendor/_laravel_idea',
        'vendor/bin',

        // ── Laravel runtime caches ──────────────────────────────────────
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/framework/testing',
        'storage/logs',
        'storage/app/public/images/temp',
        'bootstrap/cache',
        // Keep bootstrap/cache directory itself with a .gitignore so Laravel
        // doesn't blow up on first boot — handled in the build command.

        // ── Personal / dev artefacts that accumulate in storage/app/ ────
        //  These pile up during local development (plugin caches, install
        //  markers, test receipts) and contain no buyer-relevant data.
        //
        //  IMPORTANT: storage/app/public/files and storage/app/public/images
        //  ARE shipped — the seeded settings (logo, favicon, login banner,
        //  blog images, page-builder content, etc.) reference exact paths
        //  like "images/2026/05/06/foo.png". Excluding them breaks every
        //  default demo asset out of the box. Personal test uploads land
        //  there too, but the trade-off is acceptable: the buyer's first
        //  impression should be a polished demo, not a wall of broken
        //  image icons.
        'storage/app/installed',
        'storage/app/laravel-brain',
        'storage/app/purifier',
        'storage/app/project-updater-test',
        'storage/app/updates',
        // Genuinely temporary upload folders (cleared on every run anyway).
        'storage/app/public/images/temp',
        // Locally-generated PDF receipts used during template development.

        // ── Environment & secrets ───────────────────────────────────────
        '.env',
        '.env.backup',
        '.env.local',
        '.env.production',
        '.env.testing',
        'auth.json',

        // ── AI tooling / dev assistants ─────────────────────────────────
        '.claude',
        '.codex',
        '.codex.json',
        '.ai',
        '.aiassistant',
        '.agents',
        'AGENTS.md',
        'CLAUDE.md',
        'boost.json',
        'opencode.json',
        '.mcp.json',
        '.cursorrules',
        '.cursorignore',
        '.windsurfrules',

        // ── Local dev environment ───────────────────────────────────────
        'herd.yml',
        'Homestead.yaml',
        'Homestead.json',
        'docker-compose.yml',
        'docker-compose.override.yml',
        'Dockerfile',
        '.docker',
        'laradumps.yaml',

        // ── Backups & local artifacts ───────────────────────────────────
        'core.zip',
        'desktop.zip',
        'releases',
        '*.log',
        'database/database.sqlite',
        'database/database.sqlite-journal',
    ],

    /*
    |----------------------------------------------------------------------
    | Auto-strip rules (FK column heuristic)
    |----------------------------------------------------------------------
    |
    | Before honouring `strip_table_data` below, the builder inspects every
    | table's columns and adds any table containing at least one of these
    | "tenant FK" columns to the strip set automatically. This means new
    | features that follow the usual `user_id` / `merchant_id` convention
    | are stripped without you needing to remember to update this file.
    |
    | The manual list below is added on top — useful for tables that hold
    | tenant data but don't have one of these FK columns (e.g. `users`,
    | `admins`, `sessions`, queue / cache tables).
    |
    | Edge cases (e.g. an admin-configured rules table that happens to
    | carry a `user_id` column) can be exempted from auto-detection via
    | `auto_strip.never_strip` — the manual list still wins.
    */

    'auto_strip' => [

        'enabled' => true,

        'tenant_columns' => [
            'user_id',
            'admin_id',
            'merchant_id',
            'agent_id',
            'cardholder_id',
            'business_id',
            'recipient_user_id',
            'tokenable_id',
            'notifiable_id',
            'model_id',
        ],

        // Tables listed here are exempt from auto-detection — even if they
        // have a tenant FK column, their data will still ship (unless they
        // also appear in the manual strip list below).
        'never_strip' => [
            //
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Database tables to STRIP from the SQL dump (manual list)
    |----------------------------------------------------------------------
    |
    | The schema (CREATE TABLE) for these tables is still emitted so the
    | tables exist after a fresh install — only their rows are skipped.
    | Anything tenant-specific, transactional or session-related belongs
    | here. Reference data (currencies, plans, kyc templates, settings,
    | roles, permissions, etc.) is NOT listed → its rows are included.
    |
    | NOTE: Most tenant tables are already caught by the auto-detection
    | above. Use this list for tables WITHOUT a tenant FK column — e.g.
    | `users`, `admins`, `cache`, `jobs` — or to belt-and-brace specific
    | tables you want explicitly documented as tenant data.
    */

    'strip_table_data' => [

        // Account holders — installer creates the admin on fresh install
        'users',
        'admins',

        // Wallet & finance
        'wallets',
        'transactions',
        'withdraw_accounts',

        // Business profiles
        'merchants',
        'agents',
        'agent_operations',
        'businesses',
        'cardholders',

        // KYC submissions (templates are reference data and stay)
        'kyc_submissions',

        // Virtual cards
        'virtual_cards',
        'virtual_card_requests',

        // Subscriptions (plans are reference data and stay)
        'user_subscriptions',
        'subscription_transactions',

        // Payment links
        'payment_links',

        // Wallet-earn (plans are reference and stay)
        'wallet_earn_stakes',
        'wallet_earn_rewards',

        // P2P
        'p2p_offers',
        'p2p_orders',
        'p2p_disputes',
        'p2p_offer_feedback',
        'p2p_offer_promotions',
        'p2p_offer_promotion_purchases',
        'p2p_payment_accounts',

        // Mobile recharge orders (providers are reference and stay)
        'mobile_recharges',

        // Gift cards (templates are reference data and stay)
        'gift_cards',

        // Misc per-user data
        'vouchers',
        'referrals',
        'user_features',
        'tickets',
        'messages',

        // Auth tokens & sessions
        'sessions',
        'personal_access_tokens',
        'login_activities',
        'phone_verification_codes',

        // Notifications
        'notifications',
        'notification_preferences',

        // Role assignments (definitions in `roles` and `role_has_permissions`
        // ARE shipped — they're the system role catalogue)
        'model_has_roles',
        'model_has_permissions',

        // Transient / job queue / runtime cache
        'cache',
        'cache_locks',
        'failed_jobs',
        'jobs',
        'job_batches',
        'background_task_logs',
        'project_licenses',
        'project_updates',
    ],

    /*
    |----------------------------------------------------------------------
    | Where the release artefacts land
    |----------------------------------------------------------------------
    */

    'output_dir' => base_path('releases'),

    /*
    |----------------------------------------------------------------------
    | Path INSIDE the zip where the clean SQL dump is written
    |----------------------------------------------------------------------
    |
    | Matches the existing DB/digikash.sql layout that the installer
    | already imports from.
    */

    'sql_dump_path' => 'DB/digikash.sql',

    /*
    |----------------------------------------------------------------------
    | Files to SANITISE on the way into the release zip
    |----------------------------------------------------------------------
    |
    | The file is still shipped, but its content is rewritten before being
    | added to the archive. Keys are project-relative paths (forward slashes,
    | case-insensitive). Values are arrays of `regex pattern => replacement`
    | pairs applied in order.
    |
    | Used to strip developer-convenience defaults that shouldn't reach the
    | buyer's install — e.g. the demo admin email / password pre-filled on
    | the admin login form so the dev can log in with one click locally.
    */

    'sanitize_files' => [

        'resources/views/backend/auth/login.blade.php' => [
            // Drop the second arg from old('email', 'admin@coevs.com') so the
            // form shows an empty email field on the buyer's first visit.
            '#old\(\s*\'email\'\s*,\s*\'[^\']*\'\s*\)#' => "old('email')",
            // Strip the pre-filled demo password on the admin login form.
            '/\s+value="12345678"/' => '',
        ],
    ],
];
