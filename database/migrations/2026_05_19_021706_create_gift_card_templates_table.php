<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('preset_key')->default('premium');
            $table->string('background_color', 32)->nullable();
            $table->string('text_color', 32)->nullable();
            $table->string('ribbon_text')->nullable();
            $table->string('image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['active', 'draft', 'inactive'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_templates');
    }
};
