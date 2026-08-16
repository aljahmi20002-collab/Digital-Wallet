<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Lets a user pick a card design (color/theme) when requesting a virtual
 * card. The chosen value is later copied to `virtual_cards.meta.theme`
 * by the issuance flow, so the rotating theme wheel on the dashboard
 * defers to the user's choice when one is set.
 *
 * Stored as a short string instead of an enum so adding new design
 * options later (admin-defined gradients, etc.) does not require a
 * schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_card_requests', function (Blueprint $table): void {
            $table->string('theme', 32)->nullable()->after('network');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_card_requests', function (Blueprint $table): void {
            $table->dropColumn('theme');
        });
    }
};
