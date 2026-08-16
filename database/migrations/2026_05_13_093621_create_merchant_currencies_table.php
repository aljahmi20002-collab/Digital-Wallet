<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('merchant_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();

            $table->unique(['merchant_id', 'currency_id']);
            $table->index(['currency_id', 'is_primary']);
        });

        $now = now();

        DB::table('merchants')
            ->select(['id', 'currency_id'])
            ->orderBy('id')
            ->chunkById(500, function ($merchants) use ($now): void {
                $rows = $merchants
                    ->filter(fn ($merchant): bool => $merchant->currency_id !== null)
                    ->map(fn ($merchant): array => [
                        'merchant_id' => $merchant->id,
                        'currency_id' => $merchant->currency_id,
                        'is_primary'  => true,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ])
                    ->values()
                    ->all();

                if ($rows !== []) {
                    DB::table('merchant_currencies')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_currencies');
    }
};
