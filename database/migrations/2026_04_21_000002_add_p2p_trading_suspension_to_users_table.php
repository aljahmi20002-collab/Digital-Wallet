<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds P2P trader moderation columns.
     *
     * Null = trading allowed. A non-null `p2p_trading_suspended_at` means the
     * admin has suspended the user from creating new offers or accepting new
     * orders. Existing in-flight orders continue to completion.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'p2p_trading_suspended_at')) {
                $table->timestamp('p2p_trading_suspended_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'p2p_trading_suspend_reason')) {
                $table->string('p2p_trading_suspend_reason', 500)->nullable()->after('p2p_trading_suspended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'p2p_trading_suspend_reason')) {
                $table->dropColumn('p2p_trading_suspend_reason');
            }

            if (Schema::hasColumn('users', 'p2p_trading_suspended_at')) {
                $table->dropColumn('p2p_trading_suspended_at');
            }
        });
    }
};
