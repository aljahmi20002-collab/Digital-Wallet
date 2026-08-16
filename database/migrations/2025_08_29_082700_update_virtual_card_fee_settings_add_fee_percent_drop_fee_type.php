<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add percentage fee column (keeps fee_amount as fixed fee)
        Schema::table('virtual_card_fee_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('virtual_card_fee_settings', 'fee_percent')) {
                $table->decimal('fee_percent', 8, 4)->default(0)->after('fee_amount');
            }
        });

        // Drop obsolete fee_type column
        if (Schema::hasColumn('virtual_card_fee_settings', 'fee_type')) {
            Schema::table('virtual_card_fee_settings', function (Blueprint $table) {
                $table->dropColumn('fee_type');
            });
        }
    }

    public function down(): void
    {
        // Recreate fee_type column (for rollback) and drop fee_percent
        if (!Schema::hasColumn('virtual_card_fee_settings', 'fee_type')) {
            Schema::table('virtual_card_fee_settings', function (Blueprint $table) {
                $table->string('fee_type', 32)->nullable()->after('operation');
            });
        }

        if (Schema::hasColumn('virtual_card_fee_settings', 'fee_percent')) {
            Schema::table('virtual_card_fee_settings', function (Blueprint $table) {
                $table->dropColumn('fee_percent');
            });
        }
    }
};
