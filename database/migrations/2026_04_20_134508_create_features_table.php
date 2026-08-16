<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Stable internal feature key used by the feature manager, middleware, and panel guards.');
            $table->string('label')->comment('Human-readable feature name shown in the admin feature management UI.');
            $table->string('category')->default('general')->index()->comment('Catalog group used to organize feature cards in the admin UI.');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_enabled')->default(true)->index()->comment('Global master toggle for the feature across every panel.');
            $table->boolean('is_core')->default(false)->comment('Disabling a core feature typically breaks a business flow and should be confirmed by the admin.');
            $table->json('meta')->nullable()->comment('Optional provider or presentation metadata attached to the feature definition.');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
