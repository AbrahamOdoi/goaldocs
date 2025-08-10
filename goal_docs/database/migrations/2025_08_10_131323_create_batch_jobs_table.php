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
        Schema::create('batch_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('operation_type');
            $table->json('file_ids');
            $table->integer('total_files');
            $table->integer('processed_files')->default(0);
            $table->integer('successful_files')->default(0);
            $table->integer('failed_files')->default(0);
            $table->json('options')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'completed_with_errors', 'failed', 'cancelled'])->default('pending');
            $table->decimal('progress', 5, 2)->default(0.00);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['operation_type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['progress']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_jobs');
    }
};
