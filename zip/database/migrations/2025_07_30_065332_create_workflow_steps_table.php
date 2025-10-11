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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('step_order');
            $table->string('approver_type')->default('user'); // user, role, manager
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('approver_role')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('timeout_hours')->default(24);
            $table->json('actions')->nullable(); // Actions to execute on completion
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('workflow_id');
            $table->index('approver_id');
            $table->index('step_order');
            $table->index(['workflow_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
}; 