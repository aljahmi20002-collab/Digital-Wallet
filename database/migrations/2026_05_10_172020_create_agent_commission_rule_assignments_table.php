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
        Schema::create('agent_commission_rule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id');
            $table->foreignId('agent_commission_rule_id');
            $table->string('operation_type')->default('all')->index();
            $table->unsignedSmallInteger('priority')->default(100)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();

            $table->unique(['agent_id', 'agent_commission_rule_id', 'operation_type'], 'agent_rule_operation_unique');
            $table->index(['agent_id', 'status', 'operation_type'], 'agent_rule_assignment_lookup');
            $table->foreign('agent_id', 'agent_rule_assignment_agent_fk')
                ->references('id')
                ->on('agents')
                ->cascadeOnDelete();
            $table->foreign('agent_commission_rule_id', 'agent_rule_assignment_rule_fk')
                ->references('id')
                ->on('agent_commission_rules')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commission_rule_assignments');
    }
};
