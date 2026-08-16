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
        Schema::create('wallet_earn_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('minimum_amount', 24, 8);
            $table->decimal('maximum_amount', 24, 8)->nullable();
            $table->decimal('profit_rate', 24, 8);
            $table->string('profit_type')->default('percentage');
            $table->unsignedInteger('duration_value')->default(1);
            $table->string('duration_unit')->default('days');
            $table->string('payout_frequency')->default('end_of_term');
            $table->boolean('return_principal')->default(true);
            $table->boolean('auto_approve')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('plan_badge', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index('currency_id');
            $table->index(['is_featured', 'plan_badge'], 'wallet_earn_plans_featured_badge_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_earn_plans');
    }
};
