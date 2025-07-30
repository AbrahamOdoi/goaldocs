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
        Schema::table('recent_activities', function (Blueprint $table) {
            // Drop the existing enum and recreate it with new values
            $table->dropColumn('activity_type');
        });

        Schema::table('recent_activities', function (Blueprint $table) {
            $table->enum('activity_type', [
                'view', 'download', 'upload', 'edit', 'delete', 
                'share', 'permission_change', 'favorite', 'tag',
                'comment', 'workflow', 'workflow_step', 'workflow_assignment',
                'resolve_comment', 'reopen_comment', 'delete_comment'
            ])->after('folder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recent_activities', function (Blueprint $table) {
            $table->dropColumn('activity_type');
        });

        Schema::table('recent_activities', function (Blueprint $table) {
            $table->enum('activity_type', [
                'view', 'download', 'upload', 'edit', 'delete', 
                'share', 'permission_change', 'favorite', 'tag'
            ])->after('folder_id');
        });
    }
};
