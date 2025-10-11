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
        Schema::create('document_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->string('source_format');
            $table->string('target_format');
            $table->string('conversion_type');
            $table->string('output_file_path')->nullable();
            $table->bigInteger('output_file_size')->nullable();
            $table->decimal('processing_time', 8, 2)->default(0.00);
            $table->decimal('quality_score', 5, 2)->default(0.00);
            $table->json('options')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['file_id', 'status']);
            $table->index(['conversion_type', 'status']);
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
        Schema::dropIfExists('document_conversions');
    }
};
