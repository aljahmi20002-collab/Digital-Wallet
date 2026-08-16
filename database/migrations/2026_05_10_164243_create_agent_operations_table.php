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
        Schema::create('agent_operations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('agent_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('customer_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('commission_rule_id')->nullable()->constrained('agent_commission_rules')->nullOnDelete();
            $table->foreignId('agent_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('customer_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('commission_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('type')->index();
            $table->decimal('amount', 18, 8);
            $table->decimal('commission_amount', 18, 8)->default(0);
            $table->string('status')->default('completed')->index();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['agent_id', 'type', 'status']);
            $table->index(['customer_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_operations');
    }
};
