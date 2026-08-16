<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2p_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('wallet_id');
            $table->unsignedBigInteger('payer_payment_account_id')->nullable()->after('payment_method_id');
            $table->unsignedBigInteger('receiver_payment_account_id')->nullable()->after('payer_payment_account_id');
            $table->json('payer_payment_account_snapshot')->nullable()->after('receiver_payment_account_id');
            $table->json('receiver_payment_account_snapshot')->nullable()->after('payer_payment_account_snapshot');

            $table->index('payment_method_id');
            $table->index('payer_payment_account_id');
            $table->index('receiver_payment_account_id');

            $table->foreign('payment_method_id')->references('id')->on('p2p_payment_methods')->nullOnDelete();
            $table->foreign('payer_payment_account_id')->references('id')->on('p2p_payment_accounts')->nullOnDelete();
            $table->foreign('receiver_payment_account_id')->references('id')->on('p2p_payment_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('p2p_orders', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['payer_payment_account_id']);
            $table->dropForeign(['receiver_payment_account_id']);
            $table->dropIndex(['payment_method_id']);
            $table->dropIndex(['payer_payment_account_id']);
            $table->dropIndex(['receiver_payment_account_id']);
            $table->dropColumn([
                'payment_method_id',
                'payer_payment_account_id',
                'receiver_payment_account_id',
                'payer_payment_account_snapshot',
                'receiver_payment_account_snapshot',
            ]);
        });
    }
};
