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
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->onDelete('cascade');
            $table->foreignId('step_id')->constrained('workflow_steps')->onDelete('cascade');
            $table->string('action_type'); // pending, approved, rejected, expired
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('acted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('due_date');
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('workflow_instance_id');
            $table->index('step_id');
            $table->index('assigned_to');
            $table->index('acted_by');
            $table->index('action_type');
            $table->index('due_date');
            $table->index(['assigned_to', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};
