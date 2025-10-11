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
        Schema::create('annotation_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annotation_id')->constrained('document_annotations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('annotation_comments')->onDelete('cascade'); // For threaded comments
            $table->text('content');
            $table->json('mentions')->nullable(); // @mentions in the comment
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['annotation_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['parent_id', 'created_at']);
            $table->index(['is_resolved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annotation_comments');
    }
};
