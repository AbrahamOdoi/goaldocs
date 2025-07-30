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
        Schema::create('document_workflows', function (Blueprint $table) {
            $table->id();
            
            // Document being processed
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            
            // Workflow details
            $table->string('name'); // Workflow name
            $table->text('description')->nullable();
            $table->enum('type', ['approval', 'review', 'custom'])->default('approval');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            
            // Workflow configuration
            $table->json('config')->nullable(); // Workflow-specific configuration
            $table->boolean('is_template')->default(false); // Whether this is a reusable template
            
            // Organization
            $table->string('user_type'); // organization, family, individual, etc.
            $table->string('type_name'); // Specific organization/family name
            
            // Creator and management
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Primary assignee
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['file_id', 'status']);
            $table->index(['folder_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['user_type', 'type_name']);
            $table->index(['type', 'is_template']);
            $table->index('due_date');

            // Ensure either file_id or folder_id is set, but not both
            $table->unique(['file_id', 'status'], 'unique_active_file_workflow');
            $table->unique(['folder_id', 'status'], 'unique_active_folder_workflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_workflows');
    }
};
