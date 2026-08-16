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
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('category_display_name')->nullable()->after('category');
            $table->string('display_name')->nullable()->after('name');
            $table->string('description')->nullable()->after('display_name');
            $table->string('category_icon')->nullable()->after('category_display_name');
            $table->string('category_summary')->nullable()->after('category_icon');
            $table->string('category_description')->nullable()->after('category_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'description',
                'category_display_name',
                'category_icon',
                'category_summary',
                'category_description',
            ]);
        });
    }
};
