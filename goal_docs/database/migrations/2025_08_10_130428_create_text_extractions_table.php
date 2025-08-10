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
        Schema::create('text_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->string('extraction_type');
            $table->longText('extracted_text')->nullable();
            $table->json('structured_data')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('processing_time', 8, 2)->default(0.00);
            $table->decimal('quality_score', 5, 2)->default(0.00);
            $table->integer('word_count')->default(0);
            $table->integer('character_count')->default(0);
            $table->integer('page_count')->default(1);
            $table->json('options')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['file_id', 'extraction_type']);
            $table->index(['extraction_type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['quality_score']);
            $table->index(['processing_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_extractions');
    }
};
