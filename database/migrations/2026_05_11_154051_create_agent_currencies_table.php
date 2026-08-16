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
        Schema::create('agent_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();

            $table->unique(['agent_id', 'currency_id']);
            $table->index(['currency_id', 'is_primary']);
        });

        $now = now();

        DB::table('agents')
            ->select(['id', 'currency_id'])
            ->orderBy('id')
            ->chunkById(500, function ($agents) use ($now): void {
                $rows = $agents
                    ->filter(fn ($agent): bool => $agent->currency_id !== null)
                    ->map(fn ($agent): array => [
                        'agent_id'    => $agent->id,
                        'currency_id' => $agent->currency_id,
                        'is_primary'  => true,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ])
                    ->values()
                    ->all();

                if ($rows !== []) {
                    DB::table('agent_currencies')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_currencies');
    }
};
