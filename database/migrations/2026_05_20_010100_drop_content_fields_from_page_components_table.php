<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the obsolete `content_fields` JSON column.
 *
 * `content_fields` was the original (now-removed) per-row schema cache from
 * before the field-definition files in `resources/structure/page_component/`
 * became the single source of truth. Production installs have already had
 * it dropped manually (see the column listing from a current install) — this
 * migration aligns fresh dev/test databases so the schema matches everywhere
 * and INSERTs that omit it no longer trip a NOT NULL constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_components') || ! Schema::hasColumn('page_components', 'content_fields')) {
            return;
        }

        Schema::table('page_components', function (Blueprint $table) {
            $table->dropColumn('content_fields');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_components') || Schema::hasColumn('page_components', 'content_fields')) {
            return;
        }

        Schema::table('page_components', function (Blueprint $table) {
            $table->json('content_fields')->nullable();
        });
    }
};
