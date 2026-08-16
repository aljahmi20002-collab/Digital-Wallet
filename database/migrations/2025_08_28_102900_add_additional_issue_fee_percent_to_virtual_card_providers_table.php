<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            // Extra percent applied on initial load amount at issuance (e.g., 1.50 means 1.5%)
            $table->decimal('issue_fee_pct', 5, 2)
                ->nullable()
                ->after('issue_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            $table->dropColumn('issue_fee_pct');
        });
    }
};
