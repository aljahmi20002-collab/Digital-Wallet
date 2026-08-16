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
        Schema::table('virtual_card_requests', function (Blueprint $table) {
            $table->decimal('initial_load_amount', 12, 2)
                ->nullable()
                ->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_card_requests', function (Blueprint $table) {
            if (Schema::hasColumn('virtual_card_requests', 'initial_load_amount')) {
                $table->dropColumn('initial_load_amount');
            }
        });
    }
};
