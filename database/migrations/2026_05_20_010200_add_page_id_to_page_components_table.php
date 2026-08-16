<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add `page_id` to `page_components`.
 *
 * Live production databases already carry this column (added out-of-band when
 * the per-page component override feature was prototyped). Test and fresh-
 * install databases didn't — which caused PageController::edit's
 * `where('page_id', ...)` filter to fall back silently and component lists to
 * appear empty after the theme-scoped query landed. This migration brings
 * fresh schemas in line with production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_components') || Schema::hasColumn('page_components', 'page_id')) {
            return;
        }

        Schema::table('page_components', function (Blueprint $table) {
            $table->unsignedBigInteger('page_id')
                ->nullable()
                ->after('repeated_content')
                ->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_components') || ! Schema::hasColumn('page_components', 'page_id')) {
            return;
        }

        Schema::table('page_components', function (Blueprint $table) {
            $table->dropIndex(['page_id']);
            $table->dropColumn('page_id');
        });
    }
};
