<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2p_offers', function (Blueprint $table) {
            if (! $this->indexExists('p2p_offers', 'p2p_offers_status_side_idx')) {
                $table->index(['status', 'side'], 'p2p_offers_status_side_idx');
            }

            if (! $this->indexExists('p2p_offers', 'p2p_offers_user_status_idx')) {
                $table->index(['user_id', 'status'], 'p2p_offers_user_status_idx');
            }
        });

        Schema::table('p2p_orders', function (Blueprint $table) {
            if (! $this->indexExists('p2p_orders', 'p2p_orders_status_expires_idx')) {
                $table->index(['status', 'expires_at'], 'p2p_orders_status_expires_idx');
            }

            if (! $this->indexExists('p2p_orders', 'p2p_orders_maker_status_idx')) {
                $table->index(['maker_id', 'status'], 'p2p_orders_maker_status_idx');
            }

            if (! $this->indexExists('p2p_orders', 'p2p_orders_taker_status_idx')) {
                $table->index(['taker_id', 'status'], 'p2p_orders_taker_status_idx');
            }

            if (! $this->indexExists('p2p_orders', 'p2p_orders_wallet_status_idx')) {
                $table->index(['wallet_id', 'status'], 'p2p_orders_wallet_status_idx');
            }

            if (! $this->indexExists('p2p_orders', 'p2p_orders_trx_id_idx')) {
                $table->index(['trx_id'], 'p2p_orders_trx_id_idx');
            }
        });

        Schema::table('p2p_disputes', function (Blueprint $table) {
            if (! $this->indexExists('p2p_disputes', 'p2p_disputes_order_unique')) {
                $table->unique('order_id', 'p2p_disputes_order_unique');
            }

            if (! $this->indexExists('p2p_disputes', 'p2p_disputes_status_created_idx')) {
                $table->index(['status', 'created_at'], 'p2p_disputes_status_created_idx');
            }
        });

        Schema::table('p2p_payment_methods', function (Blueprint $table) {
            if (! $this->indexExists('p2p_payment_methods', 'p2p_payment_methods_status_country_idx')) {
                $table->index(['status', 'country'], 'p2p_payment_methods_status_country_idx');
            }

            if (! $this->indexExists('p2p_payment_methods', 'p2p_payment_methods_status_sort_idx')) {
                $table->index(['status', 'sort_order'], 'p2p_payment_methods_status_sort_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('p2p_payment_methods', function (Blueprint $table) {
            if ($this->indexExists('p2p_payment_methods', 'p2p_payment_methods_status_country_idx')) {
                $table->dropIndex('p2p_payment_methods_status_country_idx');
            }

            if ($this->indexExists('p2p_payment_methods', 'p2p_payment_methods_status_sort_idx')) {
                $table->dropIndex('p2p_payment_methods_status_sort_idx');
            }
        });

        Schema::table('p2p_disputes', function (Blueprint $table) {
            if ($this->indexExists('p2p_disputes', 'p2p_disputes_order_unique')) {
                $table->dropUnique('p2p_disputes_order_unique');
            }

            if ($this->indexExists('p2p_disputes', 'p2p_disputes_status_created_idx')) {
                $table->dropIndex('p2p_disputes_status_created_idx');
            }
        });

        Schema::table('p2p_orders', function (Blueprint $table) {
            if ($this->indexExists('p2p_orders', 'p2p_orders_status_expires_idx')) {
                $table->dropIndex('p2p_orders_status_expires_idx');
            }

            if ($this->indexExists('p2p_orders', 'p2p_orders_maker_status_idx')) {
                $table->dropIndex('p2p_orders_maker_status_idx');
            }

            if ($this->indexExists('p2p_orders', 'p2p_orders_taker_status_idx')) {
                $table->dropIndex('p2p_orders_taker_status_idx');
            }

            if ($this->indexExists('p2p_orders', 'p2p_orders_wallet_status_idx')) {
                $table->dropIndex('p2p_orders_wallet_status_idx');
            }

            if ($this->indexExists('p2p_orders', 'p2p_orders_trx_id_idx')) {
                $table->dropIndex('p2p_orders_trx_id_idx');
            }
        });

        Schema::table('p2p_offers', function (Blueprint $table) {
            if ($this->indexExists('p2p_offers', 'p2p_offers_status_side_idx')) {
                $table->dropIndex('p2p_offers_status_side_idx');
            }

            if ($this->indexExists('p2p_offers', 'p2p_offers_user_status_idx')) {
                $table->dropIndex('p2p_offers_user_status_idx');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");

            return collect($indexes)->contains(function (object $index) use ($indexName): bool {
                return isset($index->name) && $index->name === $indexName;
            });
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->exists();
        }

        return false;
    }
};
