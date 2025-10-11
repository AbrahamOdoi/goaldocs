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
        Schema::create('document_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('annotation_type'); // highlight, underline, strikeout, text, drawing, shape, sticky_note
            $table->integer('page_number');
            $table->json('coordinates'); // x, y, width, height, points for shapes
            $table->text('content')->nullable(); // text content for text annotations
            $table->json('style')->nullable(); // color, opacity, font, size, etc.
            $table->string('color', 7)->default('#FFEB3B'); // hex color
            $table->decimal('opacity', 3, 2)->default(1.00);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->json('metadata')->nullable(); // additional data like drawing paths, shape properties
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['file_id', 'page_number']);
            $table->index(['user_id', 'created_at']);
            $table->index(['annotation_type', 'is_resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_annotations');
    }
};
