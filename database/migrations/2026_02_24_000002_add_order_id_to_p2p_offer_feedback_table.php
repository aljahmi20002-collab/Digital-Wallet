<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2p_offer_feedback', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('offer_id');

            $table->index(['order_id']);
            $table->unique(['order_id', 'user_id']);

            $table->foreign('order_id')
                ->references('id')
                ->on('p2p_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('p2p_offer_feedback', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique(['order_id', 'user_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
