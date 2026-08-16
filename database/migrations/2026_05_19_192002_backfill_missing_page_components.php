<?php

/*
|--------------------------------------------------------------------------
| Backfill the page_components rows shipped late in the dev cycle
|--------------------------------------------------------------------------
|
| The page_components table is seeded from the SQL dump in the release zip
| — there is no per-row seeder/migration. When new shipped components are
| added AFTER an install is built, those rows are missing on existing
| production installs because they came from the older dump.
|
| This is idempotent: it only inserts rows whose component_key isn't
| already there, so re-running migrate on the seller's own machine or any
| install that already has the rows is a no-op.
|
| Add new entries to the $components array as future shipped components
| are introduced — same pattern, no schema changes.
*/

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_components')) {
            return;
        }

        $components = [
            [
                'component_key'  => 'subscription_plans',
                'component_name' => 'Subscription Plans',
                'type'           => 'static',
                'content_data'   => [
                    'heading'     => ['en' => 'Choose a plan', 'es' => ''],
                    'subheading'  => ['en' => 'Transparent pricing', 'es' => ''],
                    'description' => [
                        'en' => 'Upgrade or downgrade anytime — no hidden fees, no lock-in.',
                        'es' => '',
                    ],
                ],
            ],
            [
                'component_key'  => 'wallet_earn',
                'component_name' => 'Wallet Earn',
                'type'           => 'static',
                'content_data'   => [
                    'heading'     => ['en' => 'Put your wallet to work', 'es' => ''],
                    'subheading'  => ['en' => 'Grow your money', 'es' => ''],
                    'description' => [
                        'en' => 'Stake your funds and earn guaranteed returns. Flexible terms, transparent payouts, zero surprises.',
                        'es' => '',
                    ],
                ],
            ],
        ];

        $now              = now();
        $hasContentFields = Schema::hasColumn('page_components', 'content_fields');
        $hasIsModal       = Schema::hasColumn('page_components', 'is_modal');
        $hasSort          = Schema::hasColumn('page_components', 'sort');

        // Sort the new components AFTER everything else that already
        // ships so they don't elbow into the middle of the gallery.
        $baseSort = (int) (DB::table('page_components')->max('sort') ?? 0);

        foreach ($components as $index => $component) {
            $exists = DB::table('page_components')
                ->where('component_key', $component['component_key'])
                ->exists();

            if ($exists) {
                continue;
            }

            $row = [
                'component_name'   => $component['component_name'],
                'component_key'    => $component['component_key'],
                'content_data'     => json_encode($component['content_data']),
                'type'             => $component['type'],
                'is_active'        => 1,
                'repeated_content' => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            // Older installs may still carry the legacy NOT NULL
            // columns from the original create migration. Provide
            // safe defaults so the INSERT works on every schema
            // generation without altering tables.
            if ($hasContentFields) {
                $row['content_fields'] = '{}';
            }
            if ($hasIsModal) {
                $row['is_modal'] = 0;
            }
            if ($hasSort) {
                $row['sort'] = $baseSort + $index + 1;
            }

            DB::table('page_components')->insert($row);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_components')) {
            return;
        }

        DB::table('page_components')
            ->whereIn('component_key', ['subscription_plans', 'wallet_earn'])
            ->delete();
    }
};
