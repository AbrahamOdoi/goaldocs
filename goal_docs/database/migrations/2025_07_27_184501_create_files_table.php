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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name
            $table->string('original_name'); // Original filename when uploaded
            $table->string('file_path'); // Path to file in storage
            $table->string('file_hash')->nullable(); // For duplicate detection
            $table->bigInteger('file_size'); // Size in bytes
            $table->string('mime_type');
            $table->string('extension');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('user_type'); // organisation, family, government, etc.
            $table->unsignedBigInteger('uploaded_by');
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['folder_id', 'user_type']);
            $table->index(['uploaded_by', 'user_type']);
            $table->index(['mime_type', 'user_type']);
            $table->index('is_active');
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
