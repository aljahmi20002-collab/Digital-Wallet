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
        Schema::create('agent_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true)->index();
            $table->boolean('applies_globally')->default(false)->index();
            $table->unsignedSmallInteger('priority')->default(100)->index();
            $table->string('operation_type')->default('all')->index();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('min_amount', 18, 8)->default(0);
            $table->decimal('max_amount', 18, 8)->nullable();
            $table->string('calculation_type')->index();
            $table->decimal('percentage_rate', 8, 4)->default(0);
            $table->decimal('fixed_amount', 18, 8)->default(0);
            $table->decimal('min_commission', 18, 8)->nullable();
            $table->decimal('max_commission', 18, 8)->nullable();
            $table->timestamp('effective_from')->nullable()->index();
            $table->timestamp('effective_until')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'applies_globally', 'operation_type', 'priority'], 'agent_rules_global_lookup');
            $table->index(['currency_id', 'operation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commission_rules');
    }
};
