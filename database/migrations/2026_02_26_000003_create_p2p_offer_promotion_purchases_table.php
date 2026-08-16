<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_offer_promotion_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('trx_id')->nullable();

            $table->decimal('base_price', 24, 8)->default(0);
            $table->string('base_currency', 10);

            $table->decimal('paid_amount', 24, 8)->default(0);
            $table->string('paid_currency', 10);
            $table->decimal('exchange_rate', 24, 8)->default(0);

            $table->unsignedInteger('duration_minutes')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(['offer_id']);
            $table->index(['user_id']);
            $table->index(['trx_id']);
            $table->index(['ends_at']);

            $table->foreign('offer_id')->references('id')->on('p2p_offers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('p2p_promotion_packages')->restrictOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_offer_promotion_purchases');
    }
};
