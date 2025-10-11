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
        Schema::create('external_shares', function (Blueprint $table) {
            $table->id();
            
            // Resource being shared (file or folder)
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            
            // Share details
            $table->string('share_token', 64)->unique();
            $table->enum('share_type', ['link', 'email', 'whatsapp'])->default('link');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_name')->nullable();
            
            // Access controls
            $table->boolean('password_protected')->default(false);
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_downloads')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            
            // Permissions for external users
            $table->json('permissions');
            
            // Organization and creator
            $table->string('user_type');
            $table->string('type_name');
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['share_token']);
            $table->index(['file_id', 'folder_id']);
            $table->index(['user_type', 'type_name']);
            $table->index(['created_by']);
            $table->index(['expires_at']);
            $table->index(['is_active']);
            
            // Foreign keys
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_shares');
    }
};
