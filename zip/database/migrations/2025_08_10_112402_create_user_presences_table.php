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
        Schema::create('user_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('collaboration_sessions')->onDelete('cascade');
            $table->string('connection_id')->unique(); // Unique connection identifier
            $table->string('status')->default('online'); // online, away, busy, offline
            $table->integer('current_page')->nullable(); // Current page user is viewing
            $table->json('cursor_position')->nullable(); // User's cursor position
            $table->json('activity_data')->nullable(); // Additional activity data
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['session_id', 'status']);
            $table->index(['user_id', 'session_id']);
            $table->index(['last_seen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_presences');
    }
};
