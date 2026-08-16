<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('p2p_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wallet_id');
            $table->string('side', 10); // BUY or SELL (string, not DB enum)
            $table->decimal('price', 24, 8);
            $table->decimal('min_amount', 24, 8)->default(0);
            $table->decimal('max_amount', 24, 8)->nullable();
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE/PAUSED/DISABLED
            $table->integer('payment_window_minutes')->default(45);
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'wallet_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
        });

        Schema::create('p2p_offer_payment_method', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->timestamps();

            $table->unique(['offer_id','payment_method_id']);
            $table->foreign('offer_id')->references('id')->on('p2p_offers')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('p2p_payment_methods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_offer_payment_method');
        Schema::dropIfExists('p2p_offers');
    }
};
