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
        Schema::create('security_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // password, session, mfa, encryption, etc.
            $table->text('description');
            $table->json('settings'); // Policy configuration
            $table->boolean('is_active')->default(true);
            $table->boolean('is_global')->default(false); // Apply to all users
            $table->json('applies_to')->nullable(); // Specific users, roles, or groups
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index(['is_global', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_policies');
    }
};
