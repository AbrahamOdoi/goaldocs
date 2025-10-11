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
        Schema::create('file_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->integer('version_number');
            $table->string('file_path'); // Path to this version's file
            $table->bigInteger('file_size'); // Size of this version
            $table->string('mime_type');
            $table->unsignedBigInteger('uploaded_by');
            $table->text('change_notes')->nullable(); // What changed in this version
            $table->boolean('is_current')->default(false); // Is this the current version
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['file_id', 'version_number']);
            $table->index(['file_id', 'is_current']);
            $table->unique(['file_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_versions');
    }
};
