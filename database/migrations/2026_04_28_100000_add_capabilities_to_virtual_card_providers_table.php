<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            $table->json('capabilities')->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_card_providers', function (Blueprint $table) {
            $table->dropColumn('capabilities');
        });
    }
};
