<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task_key');
            $table->string('command_signature');
            $table->string('status')->default('running');
            $table->json('options')->nullable();
            $table->longText('output')->nullable();
            $table->longText('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('trigger_type')->default('manual');
            $table->timestamps();

            $table->index(['task_key', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_task_logs');
    }
};
