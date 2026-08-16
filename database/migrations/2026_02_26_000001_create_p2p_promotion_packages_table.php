<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_promotion_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 24, 8)->default(0);
            $table->string('base_currency', 10)->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_promotion_packages');
    }
};
