<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the breadcrumb columns the Page model and PageController have referenced
 * for a while but the original create-pages migration never created. Production
 * has them (added out-of-band at some point), but a fresh `migrate` on a new
 * environment was crashing on inserts because the columns were absent. This
 * migration is idempotent: it only adds either column if it does not exist yet,
 * so it is safe to run on environments that already have them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('pages', 'breadcrumb')) {
                $table->string('breadcrumb')->nullable()->after('type');
            }

            if (! Schema::hasColumn('pages', 'is_breadcrumb')) {
                $table->boolean('is_breadcrumb')->default(false)->after('breadcrumb');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            if (Schema::hasColumn('pages', 'is_breadcrumb')) {
                $table->dropColumn('is_breadcrumb');
            }

            if (Schema::hasColumn('pages', 'breadcrumb')) {
                $table->dropColumn('breadcrumb');
            }
        });
    }
};
