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
        Schema::create('wallet_earn_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_earn_stake_id')->constrained('wallet_earn_stakes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->decimal('amount', 24, 8);
            $table->unsignedInteger('payout_number');
            $table->timestamp('scheduled_at');
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('paid');
            $table->timestamps();

            $table->unique(['wallet_earn_stake_id', 'payout_number'], 'wallet_earn_rewards_stake_payout_unique');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_earn_rewards');
    }
};
