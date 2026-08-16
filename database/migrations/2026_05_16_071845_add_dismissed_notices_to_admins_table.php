<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            // Stores notice keys the admin has permanently dismissed from
            // the admin dashboard (e.g. "background-tasks-setup"). Once a
            // key lands in this JSON array, the banner never re-appears for
            // that admin.
            $table->json('dismissed_notices')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropColumn('dismissed_notices');
        });
    }
};
