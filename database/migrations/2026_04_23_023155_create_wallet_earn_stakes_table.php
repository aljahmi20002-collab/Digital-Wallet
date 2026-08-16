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
        Schema::create('wallet_earn_stakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_earn_plan_id')->nullable()->constrained('wallet_earn_plans')->nullOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('plan_name');
            $table->decimal('principal_amount', 24, 8);
            $table->decimal('profit_rate', 24, 8);
            $table->string('profit_type');
            $table->unsignedInteger('duration_value');
            $table->string('duration_unit');
            $table->string('payout_frequency');
            $table->boolean('return_principal')->default(true);
            $table->decimal('expected_profit', 24, 8)->default(0);
            $table->decimal('paid_profit', 24, 8)->default(0);
            $table->unsignedInteger('total_payouts')->default(1);
            $table->unsignedInteger('payouts_made')->default(0);
            $table->string('status')->default('pending');
            $table->string('trx_id')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('next_payout_at')->nullable();
            $table->timestamp('matures_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_payout_at']);
            $table->index(['currency_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_earn_stakes');
    }
};
