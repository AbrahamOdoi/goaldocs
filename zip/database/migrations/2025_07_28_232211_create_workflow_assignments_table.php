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
        Schema::create('workflow_assignments', function (Blueprint $table) {
            $table->id();
            
            // Workflow and step
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('step_id');
            
            // Assignee (polymorphic)
            $table->string('assignable_type'); // User, Position, Department
            $table->unsignedBigInteger('assignable_id');
            
            // Assignment details
            $table->enum('role', ['primary', 'secondary', 'reviewer', 'notifier'])->default('primary');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'delegated'])->default('assigned');
            
            // Assignment metadata
            $table->timestamp('assigned_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Actions and decisions
            $table->enum('action_taken', ['approved', 'rejected', 'requested_changes', 'delegated', 'no_action'])->nullable();
            $table->text('action_notes')->nullable();
            $table->json('action_metadata')->nullable(); // Additional action data
            
            // Delegation
            $table->unsignedBigInteger('delegated_to')->nullable();
            $table->text('delegation_reason')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('workflow_id')->references('id')->on('document_workflows')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('workflow_steps')->onDelete('cascade');
            $table->foreign('delegated_to')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['workflow_id', 'status']);
            $table->index(['step_id', 'status']);
            $table->index(['assignable_type', 'assignable_id', 'status']);
            $table->index(['delegated_to', 'status']);
            $table->index('due_date');

            // Ensure unique assignment per step per assignable
            $table->unique(['step_id', 'assignable_type', 'assignable_id'], 'unique_step_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_assignments');
    }
};
