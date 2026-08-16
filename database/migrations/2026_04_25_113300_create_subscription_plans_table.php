<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 24, 8)->default(0);
            $table->string('currency_code', 10)->default('USD');
            $table->string('billing_cycle')->default('monthly');
            $table->unsignedInteger('trial_days')->default(0);
            $table->unsignedInteger('grace_days')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('plan_badge', 50)->nullable();
            $table->boolean('auto_renew_default')->default(false);
            $table->string('cancellation_policy')->default('end_of_period');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index(['billing_cycle', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
