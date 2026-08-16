<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_recharge_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->nullable()->constrained('plugins')->nullOnDelete();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('driver', 64)->index();
            $table->string('logo')->nullable();
            $table->string('description', 500)->nullable();
            $table->boolean('status')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->json('supported_countries')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->decimal('fee_fixed', 18, 8)->default(0);
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->decimal('min_amount', 18, 8)->default(0);
            $table->decimal('max_amount', 18, 8)->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_recharge_providers');
    }
};
