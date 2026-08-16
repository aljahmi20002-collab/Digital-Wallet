<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('account_name');
            $table->string('account_number');
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'payment_method_id']);
            $table->index(['user_id', 'payment_method_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('p2p_payment_methods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_payment_accounts');
    }
};
