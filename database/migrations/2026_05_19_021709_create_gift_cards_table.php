<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gift_card_template_id')->nullable()->constrained('gift_card_templates')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('amount', 15, 2);

            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name');
            $table->text('message')->nullable();

            $table->enum('delivery_method', ['email', 'wallet', 'manual'])->default('email');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->enum('status', ['pending', 'scheduled', 'delivered', 'redeemed', 'expired', 'cancelled'])->default('pending');
            $table->boolean('is_active')->default(true);

            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('redeemed_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['recipient_email', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
