<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Async-issuing providers (Bitnob in particular) can return a card id
 * synchronously and then mark the card as `createdStatus: failed`
 * milliseconds later — at which point `last4`, `expiry_month`, and
 * `expiry_year` are all null because the card was never actually
 * provisioned. The previous schema enforced NOT NULL on those columns,
 * so the manager's row insert blew up with a SQL constraint violation
 * and the user lost any record of the failed attempt.
 *
 * This migration relaxes those three columns to NULL so we can persist
 * failed-issuance rows with `status = 'failed'`. The dashboard then has
 * something concrete to show, and the user can investigate / retry
 * instead of seeing a raw SQL error.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('virtual_cards')) {
            return;
        }

        // Use raw ALTERs so we don't have to hold doctrine/dbal as a hard
        // dependency on this Laravel 11 install. `MODIFY` keeps every
        // other column attribute (default, charset, etc.) intact.
        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            // Other drivers — fall back to Schema::table with doctrine/dbal
            Schema::table('virtual_cards', function (Blueprint $table): void {
                $table->string('last4', 4)->nullable()->change();
                $table->string('expiry_month', 2)->nullable()->change();
                $table->string('expiry_year', 4)->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE `virtual_cards` MODIFY `last4` VARCHAR(4) NULL');
        DB::statement('ALTER TABLE `virtual_cards` MODIFY `expiry_month` VARCHAR(2) NULL');
        DB::statement('ALTER TABLE `virtual_cards` MODIFY `expiry_year` VARCHAR(4) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('virtual_cards')) {
            return;
        }

        // Down-migration is best-effort — backfilling NOT NULL on existing
        // null rows would lose data, so we set placeholder values first.
        DB::table('virtual_cards')->whereNull('last4')->update(['last4' => '0000']);
        DB::table('virtual_cards')->whereNull('expiry_month')->update(['expiry_month' => '01']);
        DB::table('virtual_cards')->whereNull('expiry_year')->update(['expiry_year' => '2099']);

        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            Schema::table('virtual_cards', function (Blueprint $table): void {
                $table->string('last4', 4)->nullable(false)->change();
                $table->string('expiry_month', 2)->nullable(false)->change();
                $table->string('expiry_year', 4)->nullable(false)->change();
            });

            return;
        }

        DB::statement('ALTER TABLE `virtual_cards` MODIFY `last4` VARCHAR(4) NOT NULL');
        DB::statement('ALTER TABLE `virtual_cards` MODIFY `expiry_month` VARCHAR(2) NOT NULL');
        DB::statement('ALTER TABLE `virtual_cards` MODIFY `expiry_year` VARCHAR(4) NOT NULL');
    }
};
