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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            
            // Parent workflow
            $table->unsignedBigInteger('workflow_id');
            
            // Step details
            $table->string('name'); // Step name
            $table->text('description')->nullable();
            $table->integer('order'); // Step order in workflow
            $table->enum('type', ['approval', 'review', 'notification', 'action'])->default('approval');
            
            // Step configuration
            $table->json('config')->nullable(); // Step-specific configuration
            $table->enum('status', ['pending', 'active', 'completed', 'skipped', 'failed'])->default('pending');
            
            // Assignment
            $table->enum('assignee_type', ['user', 'position', 'department', 'any'])->default('user');
            $table->unsignedBigInteger('assignee_id')->nullable(); // Specific assignee
            $table->string('assignee_name')->nullable(); // For display purposes
            
            // Timing
            $table->integer('timeout_hours')->nullable(); // Auto-advance after X hours
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Actions and decisions
            $table->enum('action_taken', ['approved', 'rejected', 'requested_changes', 'delegated'])->nullable();
            $table->text('action_notes')->nullable();
            $table->unsignedBigInteger('action_by')->nullable(); // User who took action
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('workflow_id')->references('id')->on('document_workflows')->onDelete('cascade');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['workflow_id', 'order']);
            $table->index(['workflow_id', 'status']);
            $table->index(['assignee_id', 'status']);
            $table->index(['assignee_type', 'status']);
            $table->index('due_date');

            // Ensure unique order within workflow
            $table->unique(['workflow_id', 'order'], 'unique_workflow_step_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
