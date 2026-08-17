<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug')->index();
            $table->string('item_id')->nullable()->index();
            $table->text('purchase_code')->nullable();
            $table->string('license_token')->nullable();
            $table->string('buyer_username')->nullable();
            $table->string('domain')->nullable();
            $table->string('status')->default('inactive')->index();
            $table->timestamp('support_until')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['product_slug', 'domain']);
        });

        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version')->index();
            $table->string('channel')->default('stable')->index();
            $table->string('status')->default('available')->index();
            $table->text('package_url')->nullable();
            $table->string('checksum')->nullable();
            $table->text('signature')->nullable();
            $table->json('changelog')->nullable();
            $table->json('requirements')->nullable();
            $table->string('package_path')->nullable();
            $table->string('backup_path')->nullable();
            $table->timestamp('release_date')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->longText('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['version', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_updates');
        Schema::dropIfExists('project_licenses');
    }
};
