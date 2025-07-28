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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            
            // User reference
            $table->unsignedBigInteger('user_id');
            
            // Resource reference (file or folder)
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            
            // Organization context
            $table->string('user_type');
            $table->string('type_name');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['file_id']);
            $table->index(['folder_id']);
            $table->index(['user_type', 'type_name']);
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate favorites
            $table->unique(['user_id', 'file_id', 'folder_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
