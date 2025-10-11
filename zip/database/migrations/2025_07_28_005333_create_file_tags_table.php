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
        Schema::create('file_tags', function (Blueprint $table) {
            $table->id();
            
            // File reference
            $table->unsignedBigInteger('file_id');
            
            // Tag details
            $table->string('tag_name', 100);
            $table->string('color', 7)->default('#667eea'); // Hex color code
            
            // Organization and creator
            $table->string('user_type');
            $table->string('type_name');
            $table->unsignedBigInteger('created_by');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['file_id']);
            $table->index(['tag_name']);
            $table->index(['user_type', 'type_name']);
            $table->index(['created_by']);
            
            // Foreign keys
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate tags on same file
            $table->unique(['file_id', 'tag_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_tags');
    }
};
