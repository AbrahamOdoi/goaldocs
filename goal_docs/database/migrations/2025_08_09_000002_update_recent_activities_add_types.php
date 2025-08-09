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
        if (Schema::hasTable('recent_activities')) {
            Schema::table('recent_activities', function (Blueprint $table) {
                // Convert activity_type to string to support additional types like 'search', 'workflow_*'
                $table->string('activity_type', 64)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: reverting to enum would require DBAL and precise previous enum values
    }
};


