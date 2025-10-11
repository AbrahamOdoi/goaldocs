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
        Schema::create('batch_job_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_job_id')->constrained('batch_jobs')->onDelete('cascade');
            $table->text('message');
            $table->decimal('progress', 5, 2);
            $table->boolean('success')->default(true);
            $table->timestamp('processed_at');
            $table->timestamps();
            
            $table->index(['batch_job_id', 'processed_at']);
            $table->index(['success', 'processed_at']);
            $table->index(['progress']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_job_progress_logs');
    }
};
