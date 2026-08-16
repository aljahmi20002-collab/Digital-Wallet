<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Adds two presentation-only fields to virtual_card_providers so that the
 * user-facing virtual card UI can stay visually distinct as more providers
 * are onboarded.
 *
 *  - brand_color    Hex color (e.g. "#3B6FE0") that drives the card visual
 *                   theme on the user dashboard. Falls back to the rotating
 *                   theme wheel when null.
 *  - display_label  Short label (e.g. "STRO", "BITNOB", "STRIPE") rendered
 *                   as a chip on each mini card and transaction so users can
 *                   tell at a glance which provider issued/processed it.
 *
 * No business logic depends on these columns. Adding them does not require
 * any provider implementation change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            $table->string('brand_color', 16)->nullable()->after('brand');
            $table->string('display_label', 24)->nullable()->after('brand_color');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            $table->dropColumn(['brand_color', 'display_label']);
        });
    }
};
