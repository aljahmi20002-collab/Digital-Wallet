<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tag every `page_components` row with the visual theme it belongs to.
 *
 * The Theme Manager admin tool flips a global `active_theme` setting between
 * 'classic' and 'golden'; the page builder then filters its component library
 * by this column so each theme presents a distinct, non-overlapping set of
 * blocks tailored to its own field schema and Blade partials.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_components', function (Blueprint $table) {
            $table->string('theme', 32)
                ->default('classic')
                ->after('type')
                ->index();
        });

        // Backfill: every pre-existing component is part of the original
        // bootstrap/blue look — i.e. the "classic" theme.
        DB::table('page_components')->update(['theme' => 'classic']);
    }

    public function down(): void
    {
        Schema::table('page_components', function (Blueprint $table) {
            $table->dropIndex(['theme']);
            $table->dropColumn('theme');
        });
    }
};
