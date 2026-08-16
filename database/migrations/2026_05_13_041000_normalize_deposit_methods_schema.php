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
        Schema::table('deposit_methods', function (Blueprint $table): void {
            if (Schema::hasColumn('deposit_methods', 'icon') && ! Schema::hasColumn('deposit_methods', 'logo')) {
                $table->renameColumn('icon', 'logo');
            }

            if (Schema::hasColumn('deposit_methods', 'code') && ! Schema::hasColumn('deposit_methods', 'method_code')) {
                $table->renameColumn('code', 'method_code');
            }

            if (Schema::hasColumn('deposit_methods', 'min_limit') && ! Schema::hasColumn('deposit_methods', 'min_deposit')) {
                $table->renameColumn('min_limit', 'min_deposit');
            }

            if (Schema::hasColumn('deposit_methods', 'max_limit') && ! Schema::hasColumn('deposit_methods', 'max_deposit')) {
                $table->renameColumn('max_limit', 'max_deposit');
            }

            if (Schema::hasColumn('deposit_methods', 'rate') && ! Schema::hasColumn('deposit_methods', 'conversion_rate')) {
                $table->renameColumn('rate', 'conversion_rate');
            }

            if (Schema::hasColumn('deposit_methods', 'notes') && ! Schema::hasColumn('deposit_methods', 'receive_payment_details')) {
                $table->renameColumn('notes', 'receive_payment_details');
            }
        });

        Schema::table('deposit_methods', function (Blueprint $table): void {
            if (! Schema::hasColumn('deposit_methods', 'conversion_rate_live')) {
                $column = $table->boolean('conversion_rate_live')->nullable();

                if (Schema::hasColumn('deposit_methods', 'rate_type')) {
                    $column->after('rate_type');
                }
            }

            if (Schema::hasColumn('deposit_methods', 'rate_type')) {
                $table->dropColumn('rate_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_methods', function (Blueprint $table): void {
            if (Schema::hasColumn('deposit_methods', 'logo') && ! Schema::hasColumn('deposit_methods', 'icon')) {
                $table->renameColumn('logo', 'icon');
            }

            if (Schema::hasColumn('deposit_methods', 'method_code') && ! Schema::hasColumn('deposit_methods', 'code')) {
                $table->renameColumn('method_code', 'code');
            }

            if (Schema::hasColumn('deposit_methods', 'min_deposit') && ! Schema::hasColumn('deposit_methods', 'min_limit')) {
                $table->renameColumn('min_deposit', 'min_limit');
            }

            if (Schema::hasColumn('deposit_methods', 'max_deposit') && ! Schema::hasColumn('deposit_methods', 'max_limit')) {
                $table->renameColumn('max_deposit', 'max_limit');
            }

            if (Schema::hasColumn('deposit_methods', 'conversion_rate') && ! Schema::hasColumn('deposit_methods', 'rate')) {
                $table->renameColumn('conversion_rate', 'rate');
            }

            if (Schema::hasColumn('deposit_methods', 'receive_payment_details') && ! Schema::hasColumn('deposit_methods', 'notes')) {
                $table->renameColumn('receive_payment_details', 'notes');
            }

            if (Schema::hasColumn('deposit_methods', 'conversion_rate_live')) {
                $table->dropColumn('conversion_rate_live');
            }

            if (! Schema::hasColumn('deposit_methods', 'rate_type')) {
                $table->enum('rate_type', ['fixed', 'live'])->default('fixed');
            }
        });
    }
};
