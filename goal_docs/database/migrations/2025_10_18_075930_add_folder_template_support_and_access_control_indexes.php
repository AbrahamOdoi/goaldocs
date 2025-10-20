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
        // Add is_system_folder flag to folders table
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('is_system_folder')->default(false)->after('is_active');
        });

        // Add performance indexes for access control
        Schema::table('file_permissions', function (Blueprint $table) {
            $table->index(['assignable_type', 'assignable_id', 'folder_id'], 'idx_permissions_assignable_folder');
            $table->index(['assignable_type', 'assignable_id', 'file_id'], 'idx_permissions_assignable_file');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->index(['type_name', 'is_active'], 'idx_folders_type_active');
        });

        Schema::table('user_positions', function (Blueprint $table) {
            $table->index(['position_id', 'is_active'], 'idx_user_positions_position_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('user_positions', function (Blueprint $table) {
            $table->dropIndex('idx_user_positions_position_active');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex('idx_folders_type_active');
        });

        Schema::table('file_permissions', function (Blueprint $table) {
            $table->dropIndex('idx_permissions_assignable_file');
            $table->dropIndex('idx_permissions_assignable_folder');
        });

        // Remove is_system_folder column
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('is_system_folder');
        });
    }
};
