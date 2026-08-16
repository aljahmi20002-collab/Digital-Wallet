<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('p2p_disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('raised_by'); // user id
            $table->string('status', 20)->default('OPEN');
            $table->text('reason')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'raised_by']);
            $table->foreign('order_id')->references('id')->on('p2p_orders')->cascadeOnDelete();
            $table->foreign('raised_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_disputes');
    }
};
