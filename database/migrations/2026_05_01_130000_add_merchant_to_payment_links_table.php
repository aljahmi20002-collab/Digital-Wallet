<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds optional merchant linkage to the unified payment_links table.
     * When a merchant_id is set the link is branded as the merchant shop
     * and uses the merchant's currency / fee model. Without a merchant_id
     * the link behaves as a general user/agent payment link.
     */
    public function up(): void
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->unsignedBigInteger('merchant_id')->nullable()->after('user_id');
            $table->foreign('merchant_id')
                ->references('id')->on('merchants')
                ->nullOnDelete();

            // Snapshot of merchant.fee at link creation time so subsequent
            // changes to merchant.fee don't retroactively affect payments.
            $table->decimal('merchant_fee', 8, 4)->nullable()->after('max_amount');

            $table->index(['user_id', 'merchant_id', 'status'], 'payment_links_user_merchant_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->dropIndex('payment_links_user_merchant_status_idx');
            $table->dropForeign(['merchant_id']);
            $table->dropColumn(['merchant_id', 'merchant_fee']);
        });
    }
};
