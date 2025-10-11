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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // 'department' for organizations, 'role' for families, etc.
            $table->string('user_type'); // organisation, family, government, etc.
            $table->string('color', 7)->default('#696cff'); // hex color for UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
}; 