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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable');
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('tune_enabled')->default(true);
            $table->string('tune_key')->nullable();
            $table->json('custom_tune')->nullable();
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id'], 'notification_preferences_notifiable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
