<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('p2p_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('maker_id'); // offer owner
            $table->unsignedBigInteger('taker_id'); // counterparty
            $table->unsignedBigInteger('wallet_id'); // maker wallet
            $table->decimal('price', 24, 8);
            $table->decimal('amount', 24, 8);
            $table->decimal('maker_fee', 24, 8)->default(0);
            $table->decimal('taker_fee', 24, 8)->default(0);
            $table->decimal('total', 24, 8);
            $table->string('status', 20)->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('trx_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['offer_id', 'maker_id', 'taker_id']);
            $table->foreign('offer_id')->references('id')->on('p2p_offers')->cascadeOnDelete();
            $table->foreign('maker_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('taker_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_orders');
    }
};
