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
        if (Schema::hasColumn('plugins', 'code')) {
            return;
        }

        Schema::table('plugins', function (Blueprint $table): void {
            $table->string('code', 64)->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('plugins', 'code')) {
            return;
        }

        Schema::table('plugins', function (Blueprint $table): void {
            $table->dropColumn('code');
        });
    }
};
