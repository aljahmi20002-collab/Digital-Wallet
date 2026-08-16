<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('feature_label');
            $table->string('feature_value')->default('1'); // numeric limit, 'unlimited', 'enabled', 'disabled'
            $table->string('feature_type')->default('limit'); // limit | toggle | quota
            $table->string('reset_cycle')->nullable(); // null | daily | weekly | monthly
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'feature_key'], 'sub_plan_features_plan_key_unique');
            $table->index('subscription_plan_id');
            $table->index(['feature_key', 'feature_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_features');
    }
};
