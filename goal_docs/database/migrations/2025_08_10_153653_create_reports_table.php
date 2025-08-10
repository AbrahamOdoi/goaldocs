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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'document_usage',
                'processing',
                'storage',
                'user_activity',
                'custom'
            ]);
            $table->json('config')->nullable(); // Report configuration and layout
            $table->json('schedule')->nullable(); // Scheduling configuration
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('next_generation_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['is_active', 'next_generation_at']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
