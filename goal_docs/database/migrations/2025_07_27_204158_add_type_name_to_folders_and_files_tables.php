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
        Schema::table('folders', function (Blueprint $table) {
            $table->string('type_name')->nullable()->after('user_type');
            $table->index(['user_type', 'type_name']);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('type_name')->nullable()->after('user_type');
            $table->index(['user_type', 'type_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'type_name']);
            $table->dropColumn('type_name');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'type_name']);
            $table->dropColumn('type_name');
        });
    }
};
