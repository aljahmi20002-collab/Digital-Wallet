<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('qr_token', 48)->nullable()->unique()->after('agent_code');
            $table->boolean('qr_enabled')->default(true)->after('qr_token');
            $table->timestamp('qr_token_rotated_at')->nullable()->after('qr_enabled');
        });

        DB::table('agents')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($agents): void {
                foreach ($agents as $agent) {
                    do {
                        $token = 'aqr_'.Str::lower(Str::random(32));
                    } while (DB::table('agents')->where('qr_token', $token)->exists());

                    DB::table('agents')
                        ->where('id', $agent->id)
                        ->update([
                            'qr_token'            => $token,
                            'qr_token_rotated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['qr_token', 'qr_enabled', 'qr_token_rotated_at']);
        });
    }
};
