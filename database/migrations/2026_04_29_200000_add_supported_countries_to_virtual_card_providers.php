<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Each provider supports issuance only for cardholders whose billing
 * address country is on its allow-list. Stripe Issuing is US-only,
 * StroWallet supports a wide set of African + Asian countries,
 * Bitnob supports African corridors, etc.
 *
 * The admin panel uses this column to:
 *   - filter the "Select Provider" dropdown when reviewing a request,
 *     so admins never pick an incompatible provider by mistake;
 *   - badge each cardholder with the providers that *can* issue for it;
 *   - return a clear "no compatible provider" message instead of the
 *     opaque Stripe `Cardholder cannot have a billing address country
 *     of BD` error that surfaces at API time.
 *
 * Stored as JSON (ISO-2 codes). NULL means "no restriction".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table): void {
            $table->json('supported_countries')->nullable()->after('supported_currencies');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table): void {
            $table->dropColumn('supported_countries');
        });
    }
};
