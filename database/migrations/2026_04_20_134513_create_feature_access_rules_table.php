<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_access_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('panel', 32)->index()->comment('user | merchant');
            $table->boolean('is_visible')->default(true)->comment('Feature is shown in menus, dashboards, widgets for this panel.');
            $table->boolean('is_accessible')->default(true)->comment('Feature routes, actions, and API endpoints respond for this panel.');
            $table->json('conditions')->nullable()->comment('Extensible access rules: requires_kyc, requires_phone, countries_allowed, and future panel-specific constraints.');
            $table->timestamps();

            $table->unique(['feature_id', 'panel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_access_rules');
    }
};
