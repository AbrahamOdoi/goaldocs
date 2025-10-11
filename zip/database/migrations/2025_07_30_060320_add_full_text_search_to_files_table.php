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
        Schema::table('files', function (Blueprint $table) {
            // Add columns for full-text search
            $table->text('extracted_text')->nullable()->after('description');
            $table->json('search_metadata')->nullable()->after('extracted_text');
            $table->timestamp('indexed_at')->nullable()->after('search_metadata');
            
            // Add full-text search indexes
            $table->fullText(['name', 'original_name', 'description', 'extracted_text'], 'files_fulltext_search');
            $table->index(['indexed_at'], 'files_indexed_at_index');
            $table->index(['mime_type'], 'files_mime_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex('files_fulltext_search');
            $table->dropIndex('files_indexed_at_index');
            $table->dropIndex('files_mime_type_index');
            $table->dropColumn(['extracted_text', 'search_metadata', 'indexed_at']);
        });
    }
};
