<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_recharges', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_recharges', 'country')) {
                $table->string('country', 2)->nullable()->after('operator')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_recharges', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_recharges', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
