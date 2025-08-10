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
        Schema::create('ocr_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->longText('extracted_text')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->string('language', 10)->default('eng');
            $table->decimal('processing_time', 8, 2)->default(0.00);
            $table->string('ocr_engine', 50)->default('tesseract');
            $table->string('ocr_version', 20)->nullable();
            $table->json('options')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['file_id', 'status']);
            $table->index(['status', 'processed_at']);
            $table->index(['confidence_score']);
            $table->index(['language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_results');
    }
};
