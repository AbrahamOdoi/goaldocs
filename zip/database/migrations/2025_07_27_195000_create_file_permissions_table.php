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
        Schema::create('file_permissions', function (Blueprint $table) {
            $table->id();
            
            // Resource being assigned (file or folder)
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            
            // Target of assignment (polymorphic - User, Position, Department)
            $table->string('assignable_type'); // 'App\Models\User', 'App\Models\Position', 'App\Models\Department'
            $table->unsignedBigInteger('assignable_id'); // ID of the user/position/department
            
            // Permission levels (JSON for flexibility)
            $table->json('permissions'); // {view: true, download: true, edit: false, upload: false, delete: false, reshare: false, manage: false}
            
            // Permission metadata
            $table->unsignedBigInteger('assigned_by'); // User who assigned the permission
            $table->boolean('is_inherited')->default(false); // Whether this permission was inherited from parent folder
            $table->unsignedBigInteger('inherited_from_folder_id')->nullable(); // Source folder if inherited
            $table->text('notes')->nullable(); // Optional notes about the assignment
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('inherited_from_folder_id')->references('id')->on('folders')->onDelete('cascade');

            // Indexes for performance
            $table->index(['file_id', 'assignable_type', 'assignable_id']);
            $table->index(['folder_id', 'assignable_type', 'assignable_id']);
            $table->index(['assignable_type', 'assignable_id']);
            $table->index('assigned_by');
            $table->index('is_inherited');

            // Ensure either file_id or folder_id is set, but not both
            $table->unique(['file_id', 'assignable_type', 'assignable_id'], 'unique_file_assignment');
            $table->unique(['folder_id', 'assignable_type', 'assignable_id'], 'unique_folder_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_permissions');
    }
};
