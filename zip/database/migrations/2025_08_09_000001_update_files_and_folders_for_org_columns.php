<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing organization columns and soft deletes
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                if (!Schema::hasColumn('files', 'user_type_name')) {
                    $table->string('user_type_name')->nullable()->after('user_type');
                }
                if (!Schema::hasColumn('files', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
            // Add composite index if not exists (without requiring DBAL)
            $dbName = DB::getDatabaseName();
            $exists = DB::table('INFORMATION_SCHEMA.STATISTICS')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'files')
                ->where('INDEX_NAME', 'files_user_type_user_type_name_index')
                ->exists();
            if (!$exists) {
                Schema::table('files', function (Blueprint $table) {
                    $table->index(['user_type', 'user_type_name']);
                });
            }
        }

        if (Schema::hasTable('folders')) {
            Schema::table('folders', function (Blueprint $table) {
                if (!Schema::hasColumn('folders', 'type_name')) {
                    $table->string('type_name')->nullable()->after('user_type');
                }
            });
            $dbName = DB::getDatabaseName();
            $exists = DB::table('INFORMATION_SCHEMA.STATISTICS')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'folders')
                ->where('INDEX_NAME', 'folders_user_type_type_name_index')
                ->exists();
            if (!$exists) {
                Schema::table('folders', function (Blueprint $table) {
                    $table->index(['user_type', 'type_name']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                if (Schema::hasColumn('files', 'user_type_name')) {
                    $table->dropColumn('user_type_name');
                }
                if (Schema::hasColumn('files', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                $table->dropIndex(['user_type', 'user_type_name']);
            });
        }

        if (Schema::hasTable('folders')) {
            Schema::table('folders', function (Blueprint $table) {
                if (Schema::hasColumn('folders', 'type_name')) {
                    $table->dropColumn('type_name');
                }
                // Drop index if exists
                try {
                    $table->dropIndex('folders_user_type_type_name_index');
                } catch (\Throwable $e) {
                    // ignore
                }
            });
        }
    }
};


