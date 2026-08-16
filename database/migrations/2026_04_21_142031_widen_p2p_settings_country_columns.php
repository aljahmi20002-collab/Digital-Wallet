<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2p_settings', function (Blueprint $table) {
            $table->text('allowed_countries')->nullable()->change();
            $table->text('blocked_countries')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('p2p_settings', function (Blueprint $table) {
            $table->string('allowed_countries')->nullable()->change();
            $table->string('blocked_countries')->nullable()->change();
        });
    }
};
