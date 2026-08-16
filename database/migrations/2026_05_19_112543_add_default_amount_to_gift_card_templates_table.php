<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_card_templates', function (Blueprint $table) {
            $table->decimal('default_amount', 15, 2)
                ->nullable()
                ->after('ribbon_text');
        });
    }

    public function down(): void
    {
        Schema::table('gift_card_templates', function (Blueprint $table) {
            $table->dropColumn('default_amount');
        });
    }
};
