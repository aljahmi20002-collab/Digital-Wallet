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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->string('wallet_reference')->nullable()->index(); // wallet uuid of receiver, optional

            $table->string('token', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // Amount handling
            $table->decimal('amount', 18, 2)->nullable();      // null when open amount
            $table->decimal('min_amount', 18, 2)->nullable();
            $table->decimal('max_amount', 18, 2)->nullable();

            // Lifecycle controls
            $table->string('status')->default('active')->index();    // active | inactive
            $table->timestamp('expires_at')->nullable();

            // Usage limits
            $table->unsignedInteger('max_payments')->nullable();
            $table->unsignedInteger('payments_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};
