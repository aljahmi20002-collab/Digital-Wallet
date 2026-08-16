<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('billing_cycle', 20);
            $table->decimal('price', 24, 8)->default(0);
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'billing_cycle'], 'plan_price_cycle_unique');
            $table->index('billing_cycle');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIndex(['billing_cycle', 'status']);
            $table->dropColumn(['price', 'currency_code', 'billing_cycle']);
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('price', 24, 8)->default(0)->after('description');
            $table->string('currency_code', 10)->default('USD')->after('price');
            $table->string('billing_cycle')->default('monthly')->after('currency_code');
            $table->index(['billing_cycle', 'status']);
        });

        Schema::dropIfExists('subscription_plan_prices');
    }
};
