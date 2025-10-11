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
        Schema::create('document_comments', function (Blueprint $table) {
            $table->id();
            
            // Document being commented on (file or folder)
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            
            // Comment details
            $table->text('content'); // The comment text
            $table->string('type')->default('comment'); // comment, annotation, suggestion
            $table->json('metadata')->nullable(); // For annotations: position, highlight, etc.
            
            // Author and organization
            $table->unsignedBigInteger('author_id'); // User who made the comment
            $table->string('user_type'); // organization, family, individual, etc.
            $table->string('type_name'); // Specific organization/family name
            
            // Threading support
            $table->unsignedBigInteger('parent_comment_id')->nullable(); // For replies
            $table->integer('thread_level')->default(0); // Depth in thread
            
            // Status and visibility
            $table->enum('status', ['active', 'resolved', 'archived'])->default('active');
            $table->boolean('is_private')->default(false); // Private comments only visible to author and admins
            
            // Resolution tracking
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_comment_id')->references('id')->on('document_comments')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['file_id', 'status']);
            $table->index(['folder_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index(['parent_comment_id', 'thread_level']);
            $table->index(['user_type', 'type_name']);
            $table->index('created_at');

            // Ensure either file_id or folder_id is set, but not both
            $table->unique(['file_id', 'author_id', 'created_at'], 'unique_file_comment');
            $table->unique(['folder_id', 'author_id', 'created_at'], 'unique_folder_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_comments');
    }
};
