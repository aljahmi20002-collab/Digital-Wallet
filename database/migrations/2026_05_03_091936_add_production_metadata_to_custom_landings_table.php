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
        Schema::table('custom_landings', function (Blueprint $table) {
            $table->unsignedInteger('file_count')->default(0)->after('status');
            $table->unsignedBigInteger('total_size')->default(0)->after('file_count');
            $table->string('source_checksum', 64)->nullable()->after('total_size');
            $table->timestamp('published_at')->nullable()->after('source_checksum');
            $table->timestamp('html_updated_at')->nullable()->after('published_at');
            $table->timestamp('last_validated_at')->nullable()->after('html_updated_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_landings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'file_count',
                'total_size',
                'source_checksum',
                'published_at',
                'html_updated_at',
                'last_validated_at',
            ]);
        });
    }
};
