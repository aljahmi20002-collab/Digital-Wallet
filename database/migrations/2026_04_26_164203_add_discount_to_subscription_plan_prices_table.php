<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plan_prices', function (Blueprint $table) {
            $table->unsignedTinyInteger('discount')->nullable()->after('price')->comment('Discount percentage (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plan_prices', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
