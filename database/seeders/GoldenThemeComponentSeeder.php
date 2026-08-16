<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ComponentType;
use App\Enums\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the page-builder library with Golden-theme components.
 *
 * Each row mirrors a classic component_key but is tagged `theme=golden` and
 * pre-populated with luxury private-banking copy + repeated content rows
 * lifted from the design handoff. Idempotent — re-running the seeder skips
 * existing components and existing repeated rows.
 *
 *     php artisan db:seed --class=GoldenThemeComponentSeeder
 *
 * Assets:
 *   - Source SVGs live in database/seeders/assets/golden/ (committed)
 *   - Seeder copies them to storage/app/public/images/golden/ on run
 *   - DB stores paths as `images/golden/<file>.svg`; the public .htaccess
 *     rewrites /images/... → /storage/images/... so asset() resolves them
 *     without a separate symlink.
 */
class GoldenThemeComponentSeeder extends Seeder
{
    /** Where the source assets live (committed). */
    protected string $assetSource = '';

    /** Where the assets need to land at runtime (served via storage symlink). */
    protected string $assetTarget = '';

    /** Relative path used in DB rows (resolved by .htaccess rewrite). */
    protected string $assetPublicPrefix = 'images/golden';

    public function run(): void
    {
        if (! Schema::hasTable('page_components')) {
            return;
        }

        $this->assetSource = base_path('database/seeders/assets/golden');
        $this->assetTarget = storage_path('app/public/images/golden');

        $this->copyAssets();
        $this->migrateSeededAssetPaths();
        $this->seedComponents();
    }

    /**
     * Swap previously-seeded asset paths to their current equivalents.
     *
     * mergeContentData() deliberately preserves existing values to respect
     * admin edits — but for rows that still hold a *seeded* path verbatim,
     * we must be able to bump them to a newer asset (e.g. swapping the
     * generic monogram portrait for the new wallet-mockup composition).
     * Each entry is a `[component_key, content_data field, old → new]` swap;
     * admin-uploaded paths (anything outside `images/golden/`) are never touched.
     */
    protected function migrateSeededAssetPaths(): void
    {
        $migrations = [
            // Golden "about" portrait — generic monogram → premium wallet mockup
            ['about', 'portrait_image', 'images/golden/about-portrait.svg', 'images/golden/about-wallet-mockup.svg'],
        ];

        foreach ($migrations as [$key, $field, $from, $to]) {
            $row = DB::table('page_components')
                ->where('component_key', $key)
                ->where('theme', Theme::Golden->value)
                ->first();

            if (! $row) {
                continue;
            }

            $data = json_decode((string) $row->content_data, true) ?: [];
            if (($data[$field] ?? null) !== $from) {
                continue;
            }

            $data[$field] = $to;
            DB::table('page_components')
                ->where('id', $row->id)
                ->update([
                    'content_data' => json_encode($data),
                    'updated_at'   => now(),
                ]);
        }
    }

    /**
     * Copy every SVG/PNG from the committed asset folder to the public
     * storage folder. Idempotent — overwrites are fine (file contents are
     * deterministic and committed in source control).
     */
    protected function copyAssets(): void
    {
        $fs = new Filesystem;

        if (! $fs->isDirectory($this->assetSource)) {
            $this->command?->warn("[GoldenThemeComponentSeeder] asset source missing: {$this->assetSource}");

            return;
        }

        $fs->ensureDirectoryExists($this->assetTarget);

        foreach ($fs->files($this->assetSource) as $file) {
            $dest = $this->assetTarget.DIRECTORY_SEPARATOR.$file->getFilename();
            $fs->copy($file->getPathname(), $dest);
        }
    }

    /** Resolve a stored DB-path for one of the committed assets. */
    protected function asset(string $filename): string
    {
        return $this->assetPublicPrefix.'/'.$filename;
    }

    protected function seedComponents(): void
    {
        $components = $this->components();

        $now             = now();
        $hasContentField = Schema::hasColumn('page_components', 'content_fields');
        $hasIsModal      = Schema::hasColumn('page_components', 'is_modal');
        $hasSort         = Schema::hasColumn('page_components', 'sort');
        $hasTheme        = Schema::hasColumn('page_components', 'theme');

        $baseSort = (int) (DB::table('page_components')->max('sort') ?? 0);

        foreach ($components as $index => $component) {
            $existing = DB::table('page_components')
                ->where('component_key', $component['component_key'])
                ->when($hasTheme, fn ($q) => $q->where('theme', Theme::Golden->value))
                ->first();

            if ($existing) {
                // Component already seeded — merge any newly-added fields
                // (e.g. an image path added in a later seeder revision) into
                // the existing content_data without overwriting admin edits.
                $existingData = json_decode((string) $existing->content_data, true) ?: [];
                $merged       = $this->mergeContentData($existingData, $component['content_data'] ?? []);

                if ($merged !== $existingData) {
                    DB::table('page_components')
                        ->where('id', $existing->id)
                        ->update([
                            'content_data' => json_encode($merged),
                            'updated_at'   => $now,
                        ]);
                }

                $this->seedRepeatedContent((int) $existing->id, $component['repeated_content'] ?? []);

                continue;
            }

            $row = [
                'component_name'   => $component['component_name'],
                'component_key'    => $component['component_key'],
                'content_data'     => json_encode($component['content_data'] ?? []),
                'type'             => $component['type'] ?? ComponentType::Static->value,
                'is_active'        => 1,
                'repeated_content' => ! empty($component['repeated_content']) ? 1 : 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if ($hasTheme) {
                $row['theme'] = Theme::Golden->value;
            }
            if ($hasContentField) {
                $row['content_fields'] = '{}';
            }
            if ($hasIsModal) {
                $row['is_modal'] = 0;
            }
            if ($hasSort) {
                $row['sort'] = $baseSort + $index + 1;
            }

            $componentId = DB::table('page_components')->insertGetId($row);

            $this->seedRepeatedContent((int) $componentId, $component['repeated_content'] ?? []);
        }
    }

    /**
     * Merge fresh seeder defaults into existing admin-edited content_data.
     *
     * Rule: only fill in keys that don't exist yet or were previously empty
     * — never clobber a value the admin has already set. This lets re-running
     * the seeder backfill new fields (like a newly-added image) onto rows
     * that predate them, without trampling customised copy.
     *
     * @param  array<string,mixed> $existing
     * @param  array<string,mixed> $fresh
     * @return array<string,mixed>
     */
    protected function mergeContentData(array $existing, array $fresh): array
    {
        foreach ($fresh as $key => $value) {
            if (! array_key_exists($key, $existing)) {
                $existing[$key] = $value;

                continue;
            }

            $current = $existing[$key];

            if (is_array($current) && is_array($value)) {
                $existing[$key] = $this->mergeContentData($current, $value);
            } elseif ($current === '' || $current === null) {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    /**
     * Idempotently insert repeated content rows for a component.
     * Skips inserting if any rows already exist for this component.
     *
     * @param list<array<string,mixed>> $rows
     */
    protected function seedRepeatedContent(int $componentId, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $alreadySeeded = DB::table('page_component_repeated_contents')
            ->where('component_id', $componentId)
            ->exists();

        if ($alreadySeeded) {
            return;
        }

        $now     = now();
        $payload = collect($rows)->map(fn (array $row) => [
            'component_id' => $componentId,
            'content_data' => json_encode($row),
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        DB::table('page_component_repeated_contents')->insert($payload);
    }

    /**
     * @return list<array{
     *   component_key:string,
     *   component_name:string,
     *   type?:string,
     *   content_data?:array<string,mixed>,
     *   repeated_content?:list<array<string,mixed>>
     * }>
     */
    protected function components(): array
    {
        return [
            // ------------------------------------------------------------
            // BANNER
            // ------------------------------------------------------------
            [
                'component_key'  => 'banner',
                'component_name' => 'Golden Hero',
                'content_data'   => [
                    'eyebrow'               => ['en' => 'Private Digital Wealth'],
                    'heading'               => ['en' => "A discreet vault for the\nmodern __connoisseur__ of capital."],
                    'description'           => ['en' => 'DigiKash is a private, fully-licensed digital wallet engineered for those who measure wealth in decades, not quarters. Hold, grow and move capital across borders with the composure of a century-old bank — rendered weightless.'],
                    'primary_button_text'   => ['en' => 'Open Private Wallet'],
                    'primary_button_url'    => '/user/register',
                    'secondary_button_text' => ['en' => 'Request Invitation'],
                    'secondary_button_url'  => '/contact',
                    'vault_brand'           => 'DIGIKASH',
                    'vault_tier'            => 'PRIVATE · BLACK',
                    'vault_monogram'        => 'DK',
                    'vault_number'          => '4519 •••• •••• 2208',
                    'vault_holder'          => 'A. WHITLOCK',
                    'vault_expires'         => '08 / 31',
                    'vault_balance'         => '$ 248,310.06',
                    'vault_yield'           => '12.50%',
                ],
            ],

            // ------------------------------------------------------------
            // ABOUT — portrait + 3 pillars
            // ------------------------------------------------------------
            [
                'component_key'  => 'about',
                'component_name' => 'Golden About',
                'content_data'   => [
                    'eyebrow'        => ['en' => 'About DigiKash'],
                    'heading'        => ['en' => 'The Standard of __Digital Wealth.__'],
                    'description'    => ['en' => "We are custodians, not merely a platform. Every facet of DigiKash — from cold-storage architecture to the calligraphy of a statement — is shaped by a single conviction: that tomorrow's fortunes deserve the patience and precision of the great houses of finance."],
                    'portrait_image' => $this->asset('about-wallet-mockup.svg'),
                    'button_text'    => ['en' => 'Discover Our Story'],
                    'button_url'     => '/about',
                    'stat_value'     => '12M+',
                    'stat_label'     => ['en' => 'Private Members'],
                    'stat_icon'      => 'fa-solid fa-users',
                ],
                'repeated_content' => [
                    [
                        'pillar_icon_class' => 'fa-solid fa-shield-halved',
                        'pillar_title'      => ['en' => 'Sovereign Custody'],
                        'pillar_text'       => ['en' => "Multi-signature cold vaults insured by Lloyd's syndicates — your keys answer to you alone."],
                    ],
                    [
                        'pillar_icon_class' => 'fa-solid fa-chart-line',
                        'pillar_title'      => ['en' => 'Composed Growth'],
                        'pillar_text'       => ['en' => 'Curated yield programmes calibrated by tenured strategists — never algorithmic, always deliberate.'],
                    ],
                    [
                        'pillar_icon_class' => 'fa-solid fa-vault',
                        'pillar_title'      => ['en' => 'Discreet Access'],
                        'pillar_text'       => ['en' => 'A concierge available across twelve time zones. No phone trees, no scripts, no surprises.'],
                    ],
                ],
            ],

            // ------------------------------------------------------------
            // SERVICES — 6 carousel cards
            // ------------------------------------------------------------
            [
                'component_key'  => 'service',
                'component_name' => 'Golden Services',
                'content_data'   => [
                    'eyebrow' => ['en' => 'Our Services'],
                    'heading' => ['en' => "A Suite Tailored to the\nConsidered __Few.__"],
                ],
                'repeated_content' => [
                    ['service_icon_class' => 'fa-solid fa-vault',          'service_title' => ['en' => 'Sovereign Wallet'],     'service_text' => ['en' => 'A self-custodied vault for fiat and digital assets, sealed with biometric multi-factor protocol.'], 'service_link' => '#'],
                    ['service_icon_class' => 'fa-solid fa-globe',          'service_title' => ['en' => 'Cross-Border Transfer'], 'service_text' => ['en' => 'Move capital in 38 currencies with private-banking discretion and same-day settlement.'],          'service_link' => '#'],
                    ['service_icon_class' => 'fa-solid fa-coins',          'service_title' => ['en' => 'Yield Programmes'],      'service_text' => ['en' => 'Curated annual yields from 6% to 14% — vetted by our investment council, never automated.'],           'service_link' => '#'],
                    ['service_icon_class' => 'fa-solid fa-credit-card',    'service_title' => ['en' => 'Private Card'],          'service_text' => ['en' => 'The DigiKash Black — a metal card in seven finishes, accepted in 211 countries.'],                    'service_link' => '#'],
                    ['service_icon_class' => 'fa-solid fa-scale-balanced', 'service_title' => ['en' => 'Estate Trust'],          'service_text' => ['en' => 'Multi-generational succession plans, codified on-chain and notarised across three jurisdictions.'],     'service_link' => '#'],
                    ['service_icon_class' => 'fa-solid fa-handshake',      'service_title' => ['en' => 'Concierge Advisory'],    'service_text' => ['en' => 'A dedicated relationship director available across twelve time zones, day and night.'],          'service_link' => '#'],
                ],
            ],

            // ------------------------------------------------------------
            // FEATURES — 6 tiles
            // ------------------------------------------------------------
            [
                'component_key'  => 'feature',
                'component_name' => 'Golden Features',
                'content_data'   => [
                    'eyebrow' => ['en' => 'Why DigiKash'],
                    'heading' => ['en' => 'An Heirloom Reimagined as __Software.__'],
                ],
                'repeated_content' => [
                    ['feature_number' => '01', 'feature_icon_class' => 'fa-solid fa-shield-halved', 'feature_title' => ['en' => 'Military-Grade Custody'],   'feature_text' => ['en' => 'Air-gapped vaults across three continents, audited quarterly by Big Four firms.']],
                    ['feature_number' => '02', 'feature_icon_class' => 'fa-solid fa-bolt',          'feature_title' => ['en' => 'Instantaneous Settlement'], 'feature_text' => ['en' => 'Move six-figure sums in under twelve seconds, with zero perceptible friction.']],
                    ['feature_number' => '03', 'feature_icon_class' => 'fa-solid fa-user-tie',      'feature_title' => ['en' => 'Personal Director'],        'feature_text' => ['en' => 'A named relationship director, accessible by encrypted line — never a queue.']],
                    ['feature_number' => '04', 'feature_icon_class' => 'fa-solid fa-chart-pie',     'feature_title' => ['en' => 'Composed Portfolios'],      'feature_text' => ['en' => 'Curated allocations across fiat, digital and metals — assembled, never auto-generated.']],
                    ['feature_number' => '05', 'feature_icon_class' => 'fa-solid fa-globe',         'feature_title' => ['en' => 'Borderless by Design'],     'feature_text' => ['en' => 'Hold and remit in 38 currencies; settle in 211 territories without intermediary fees.']],
                    ['feature_number' => '06', 'feature_icon_class' => 'fa-solid fa-eye-slash',     'feature_title' => ['en' => 'Absolute Discretion'],      'feature_text' => ['en' => 'Identity-shielded transfers, sealed statements, and no data resale — ever.']],
                ],
            ],

            // ------------------------------------------------------------
            // SPECIAL FEATURES — 6 blurbs (triptych: 3 left, 3 right)
            // ------------------------------------------------------------
            [
                'component_key'  => 'special_feature',
                'component_name' => 'Golden Special Features',
                'content_data'   => [
                    'eyebrow'        => ['en' => 'Inside the Wallet'],
                    'heading'        => ['en' => 'A Single Pane for __All Your Standing.__'],
                    'phone_greeting' => ['en' => 'Good evening'],
                    'phone_name'     => 'Mr. Whitlock',
                    'phone_balance'  => '$ 248,310.06',
                ],
                'repeated_content' => [
                    ['spfeat_icon_class' => 'fa-solid fa-fingerprint',   'spfeat_title' => ['en' => 'Biometric Seal'],     'spfeat_text' => ['en' => 'Face, fingerprint and voiceprint, layered into a single signature.']],
                    ['spfeat_icon_class' => 'fa-solid fa-coins',         'spfeat_title' => ['en' => 'Curated Yields'],     'spfeat_text' => ['en' => 'Hand-picked yield instruments from 6% to 14%, reviewed monthly.']],
                    ['spfeat_icon_class' => 'fa-solid fa-paper-plane',   'spfeat_title' => ['en' => 'Discreet Transfer'],  'spfeat_text' => ['en' => "Move capital without revealing your counterparty's identity."]],
                    ['spfeat_icon_class' => 'fa-solid fa-chart-line',    'spfeat_title' => ['en' => 'Live Portfolio'],     'spfeat_text' => ['en' => 'Composed allocations across fiat, gold and digital assets — at a glance.']],
                    ['spfeat_icon_class' => 'fa-solid fa-shield-halved', 'spfeat_title' => ['en' => 'Sovereign Custody'],  'spfeat_text' => ['en' => "Cold vaults insured by Lloyd's, multi-signature by default."]],
                    ['spfeat_icon_class' => 'fa-solid fa-headset',       'spfeat_title' => ['en' => '24/7 Concierge'],     'spfeat_text' => ['en' => 'A named director on the line within ninety seconds — every hour.']],
                ],
            ],

            // ------------------------------------------------------------
            // WALLET EARN (plans come from WalletEarnPlan model)
            // ------------------------------------------------------------
            [
                'component_key'  => 'wallet_earn',
                'component_name' => 'Golden Wallet Earn',
                'content_data'   => [
                    'eyebrow'     => ['en' => 'Grow Your Capital'],
                    'heading'     => ['en' => 'Wallet Earn __Programmes.__'],
                    'description' => ['en' => 'Three curated yield instruments, each calibrated to a different temperament. All returns are net, all principal is insured, all approvals are personal.'],
                ],
            ],

            // ------------------------------------------------------------
            // SUBSCRIPTION PLANS (tiers come from SubscriptionPlan model)
            // ------------------------------------------------------------
            [
                'component_key'  => 'subscription_plans',
                'component_name' => 'Golden Membership Tiers',
                'content_data'   => [
                    'eyebrow'     => ['en' => 'Membership Tiers'],
                    'heading'     => ['en' => 'Choose Your __Caliber.__'],
                    'description' => ['en' => ''],
                ],
            ],

            // ------------------------------------------------------------
            // OFFER — 4 counters
            // ------------------------------------------------------------
            [
                'component_key'  => 'offer',
                'component_name' => 'Golden Counters',
                'content_data'   => [
                    'eyebrow'     => ['en' => 'A Quiet Promise'],
                    'heading'     => ['en' => 'Where __Discretion__ Meets Performance.'],
                    'description' => ['en' => 'Join twelve million members who measure prosperity in poise. Applications are reviewed personally — never algorithmically.'],
                    'button_text' => ['en' => 'Open Private Wallet'],
                    'button_url'  => '/user/register',
                ],
                'repeated_content' => [
                    ['counter_prefix' => '',  'counter_number' => '12',   'counter_suffix' => 'M+', 'counter_decimals' => '0', 'counter_title' => ['en' => 'Private Members']],
                    ['counter_prefix' => '',  'counter_number' => '99.9', 'counter_suffix' => '%',  'counter_decimals' => '1', 'counter_title' => ['en' => 'Uptime, Five Years']],
                    ['counter_prefix' => '$', 'counter_number' => '4.2',  'counter_suffix' => 'B',  'counter_decimals' => '1', 'counter_title' => ['en' => 'Assets Under Custody']],
                    ['counter_prefix' => '',  'counter_number' => '180',  'counter_suffix' => '+',  'counter_decimals' => '0', 'counter_title' => ['en' => 'Jurisdictions Served']],
                ],
            ],

            // ------------------------------------------------------------
            // WORK PROCESS — 4 steps
            // ------------------------------------------------------------
            [
                'component_key'  => 'work_process',
                'component_name' => 'Golden Onboarding',
                'content_data'   => [
                    'eyebrow' => ['en' => 'The Onboarding'],
                    'heading' => ['en' => 'Four Considered __Steps.__'],
                ],
                'repeated_content' => [
                    ['step_number' => '01', 'step_icon_class' => 'fa-solid fa-envelope-open-text', 'step_title' => ['en' => 'Request Invitation'], 'step_description' => ['en' => 'A brief, encrypted introduction to determine fit.']],
                    ['step_number' => '02', 'step_icon_class' => 'fa-solid fa-id-card',            'step_title' => ['en' => 'Verify Identity'],    'step_description' => ['en' => 'Biometric and document review, completed in under nine minutes.']],
                    ['step_number' => '03', 'step_icon_class' => 'fa-solid fa-vault',              'step_title' => ['en' => 'Activate Wallet'],    'step_description' => ['en' => 'Sovereign keys generated and sealed within your private vault.']],
                    ['step_number' => '04', 'step_icon_class' => 'fa-solid fa-champagne-glasses', 'step_title' => ['en' => 'Begin Earning'],      'step_description' => ['en' => 'Allocate, transfer, or simply hold — your director awaits.']],
                ],
            ],

            // ------------------------------------------------------------
            // TESTIMONIAL — 3 clients
            // ------------------------------------------------------------
            [
                'component_key'  => 'testimonial',
                'component_name' => 'Golden Testimony',
                'content_data'   => [
                    'eyebrow' => ['en' => 'Testimony'],
                    'heading' => ['en' => 'Voices From the __Membership.__'],
                ],
                'repeated_content' => [
                    [
                        'client_image'    => $this->asset('testimonial-1.svg'),
                        'client_name'     => ['en' => 'Adrien Whitlock'],
                        'client_position' => ['en' => 'Chairman · Whitlock Holdings'],
                        'rating'          => '5',
                        'comment_text'    => ['en' => 'DigiKash carries the patience of a private bank and the precision of a Swiss movement. Three years in, I have yet to encounter a single click that felt rushed.'],
                    ],
                    [
                        'client_image'    => $this->asset('testimonial-2.svg'),
                        'client_name'     => ['en' => 'Margaux Le Roy'],
                        'client_position' => ['en' => 'Family Office Principal'],
                        'rating'          => '5',
                        'comment_text'    => ['en' => "My director answers within ninety seconds, signs his messages, and remembers my daughters' names. This is wealth tooling as it ought to be — quiet, attentive, exact."],
                    ],
                    [
                        'client_image'    => $this->asset('testimonial-3.svg'),
                        'client_name'     => ['en' => 'Yusuf Demir'],
                        'client_position' => ['en' => 'Trustee, Demir Heritage Foundation'],
                        'rating'          => '5',
                        'comment_text'    => ['en' => 'We tested four custodians for our succession trust. DigiKash was the only one whose statement of holdings I would frame. The yield was simply the dividend on that taste.'],
                    ],
                ],
            ],

            // ------------------------------------------------------------
            // TEAM — 3 stewards
            // ------------------------------------------------------------
            [
                'component_key'  => 'team',
                'component_name' => 'Golden Stewards',
                'content_data'   => [
                    'eyebrow' => ['en' => 'The Stewards'],
                    'heading' => ['en' => 'A Council of __Custodians.__'],
                ],
                'repeated_content' => [
                    [
                        'team_image'   => $this->asset('team-1.svg'),
                        'name'         => ['en' => 'Elena Marchetti'],
                        'designation'  => ['en' => 'Chief Custodian'],
                        'linkedin_url' => '#',
                        'twitter_url'  => '#',
                        'facebook_url' => '',
                        'email'        => 'elena@digikash.com',
                    ],
                    [
                        'team_image'   => $this->asset('team-2.svg'),
                        'name'         => ['en' => 'Soren Halvorsen'],
                        'designation'  => ['en' => 'Head of Yield Council'],
                        'linkedin_url' => '#',
                        'twitter_url'  => '#',
                        'facebook_url' => '',
                        'email'        => 'soren@digikash.com',
                    ],
                    [
                        'team_image'   => $this->asset('team-3.svg'),
                        'name'         => ['en' => 'Aisha Okonkwo'],
                        'designation'  => ['en' => 'Director, Concierge'],
                        'linkedin_url' => '#',
                        'twitter_url'  => '#',
                        'facebook_url' => '',
                        'email'        => 'aisha@digikash.com',
                    ],
                ],
            ],

            // ------------------------------------------------------------
            // BLOG (posts come from Blog model)
            // ------------------------------------------------------------
            [
                'component_key'  => 'blog',
                'component_name' => 'Golden Dispatches',
                'content_data'   => [
                    'eyebrow'     => ['en' => 'Dispatches'],
                    'heading'     => ['en' => "From the __Editor's__ Desk."],
                    'button_text' => ['en' => 'View All'],
                    'button_url'  => '/blog',
                ],
            ],

            // ------------------------------------------------------------
            // CONTACT
            // ------------------------------------------------------------
            [
                'component_key'  => 'contact',
                'component_name' => 'Golden Concierge',
                'content_data'   => [
                    'eyebrow'        => ['en' => 'Concierge'],
                    'heading'        => ['en' => 'Begin a Confidential __Conversation.__'],
                    'description'    => ['en' => 'Share a moment with our private director. Every message is read, every reply is signed.'],
                    'visual_image'   => $this->asset('contact-concierge.svg'),
                    'visual_caption' => ['en' => '24 / 7 Concierge'],
                ],
            ],

            // ------------------------------------------------------------
            // PAYMENT PARTNER (logos come from PaymentGateway model)
            // ------------------------------------------------------------
            [
                'component_key'  => 'payment_partner',
                'component_name' => 'Golden Payment Partners',
                'content_data'   => [
                    'section_heading' => ['en' => 'Trusted by Global Payment Networks'],
                ],
            ],

            // ------------------------------------------------------------
            // NEWSLETTER
            // ------------------------------------------------------------
            [
                'component_key'  => 'subscribed',
                'component_name' => 'Golden Newsletter',
                'content_data'   => [
                    'eyebrow'     => ['en' => 'The Quarterly Dispatch'],
                    'heading'     => ['en' => 'Receive Our __Quarterly__ Dispatch.'],
                    'description' => ['en' => 'Four times a year, a private letter on markets, custody, and the craft of preserving wealth. Never shared, never sold.'],
                    'button_text' => ['en' => 'Subscribe'],
                ],
            ],

            // ------------------------------------------------------------
            // DYNAMIC block
            // ------------------------------------------------------------
            [
                'component_key'  => 'dynamic',
                'component_name' => 'Golden Dynamic Block',
                'type'           => ComponentType::Dynamic->value,
                'content_data'   => [
                    'content' => ['en' => '<p>Your custom HTML content here.</p>'],
                ],
            ],
        ];
    }
}
