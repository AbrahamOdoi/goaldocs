<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Folder;
use App\Models\File;
use App\Models\Department;
use App\Models\Position;
use App\Models\FilePermission;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $folder;
    protected $file;
    protected $department;
    protected $position;
    protected $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        
        $this->adminUser = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization',
            'is_admin' => true,
            'email_verified_at' => now()
        ]);

        $this->regularUser = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization',
            'is_admin' => false,
            'email_verified_at' => now()
        ]);

        $this->department = Department::factory()->create([
            'user_type' => 'organisation',
            'type' => 'Test Organization',
            'created_by' => $this->adminUser->id
        ]);

        $this->position = Position::factory()->create([
            'department_id' => $this->department->id,
            'created_by' => $this->adminUser->id
        ]);

        $this->folder = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->adminUser->id
        ]);

        $this->file = File::factory()->create([
            'folder_id' => $this->folder->id,
            'user_type' => 'organisation',
            'uploaded_by' => $this->adminUser->id
        ]);

        $this->permissionService = new PermissionService();
    }

    public function test_direct_user_permission_assignment()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id,
            'permissions' => [
                'view' => true,
                'download' => true,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify permission was created
        $this->assertDatabaseHas('file_permissions', [
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id
        ]);

        // Test permission checking
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'download'));
        $this->assertFalse($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'edit'));
    }

    public function test_position_permission_assignment()
    {
        $this->actingAs($this->adminUser);

        // Assign user to position
        $this->regularUser->positions()->attach($this->position->id, ['is_primary' => true]);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'position',
            'assignable_id' => $this->position->id,
            'permissions' => [
                'view' => true,
                'edit' => true,
                'upload' => true
            ]
        ]);

        $response->assertStatus(200);

        // Test user inherits position permissions
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'edit'));
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'upload'));
    }

    public function test_department_permission_assignment()
    {
        $this->actingAs($this->adminUser);

        // Assign user to position in department
        $this->regularUser->positions()->attach($this->position->id, ['is_primary' => true]);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'department',
            'assignable_id' => $this->department->id,
            'permissions' => [
                'view' => true,
                'download' => true
            ]
        ]);

        $response->assertStatus(200);

        // Test user inherits department permissions
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'download'));
    }

    public function test_permission_conflict_resolution()
    {
        $this->actingAs($this->adminUser);

        $position2 = Position::factory()->create([
            'department_id' => $this->department->id,
            'created_by' => $this->adminUser->id
        ]);

        // Assign user to multiple positions
        $this->regularUser->positions()->attach([
            $this->position->id => ['is_primary' => true],
            $position2->id => ['is_primary' => false]
        ]);

        // Position 1: Can edit
        $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'position',
            'assignable_id' => $this->position->id,
            'permissions' => ['edit' => true]
        ]);

        // Position 2: Cannot edit
        $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'position',
            'assignable_id' => $position2->id,
            'permissions' => ['edit' => false]
        ]);

        // Should use most permissive rule
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->folder, 'edit'));
    }

    public function test_bulk_permission_assignment()
    {
        $this->actingAs($this->adminUser);

        $folder2 = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->adminUser->id
        ]);

        $response = $this->post('/files/permissions/bulk-assign', [
            'assignables' => [
                [
                    'type' => 'user',
                    'id' => $this->regularUser->id
                ]
            ],
            'resources' => [
                [
                    'type' => 'folder',
                    'id' => $this->folder->id
                ],
                [
                    'type' => 'folder',
                    'id' => $folder2->id
                ]
            ],
            'permissions' => [
                'view' => true,
                'download' => true
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify permissions were created for both folders
        $this->assertDatabaseHas('file_permissions', [
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id
        ]);

        $this->assertDatabaseHas('file_permissions', [
            'folder_id' => $folder2->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id
        ]);
    }

    public function test_permission_copying()
    {
        $this->actingAs($this->adminUser);

        $sourceFolder = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->adminUser->id
        ]);

        $targetFolder = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->adminUser->id
        ]);

        // Create permission on source folder
        FilePermission::create([
            'folder_id' => $sourceFolder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id,
            'permissions' => json_encode(['view' => true, 'edit' => true]),
            'assigned_by' => $this->adminUser->id
        ]);

        $response = $this->post('/files/permissions/copy', [
            'source_type' => 'folder',
            'source_id' => $sourceFolder->id,
            'target_type' => 'folder',
            'target_id' => $targetFolder->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify permission was copied
        $this->assertDatabaseHas('file_permissions', [
            'folder_id' => $targetFolder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id
        ]);
    }

    public function test_access_report_generation()
    {
        $this->actingAs($this->adminUser);

        // Create some permissions
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id,
            'permissions' => json_encode(['view' => true, 'edit' => true]),
            'assigned_by' => $this->adminUser->id
        ]);

        $response = $this->get("/files/permissions/report/{$this->folder->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'report' => [
                'direct_permissions',
                'inherited_permissions',
                'summary'
            ]
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['report']['direct_permissions']);
    }

    public function test_permission_validation()
    {
        $this->actingAs($this->adminUser);

        // Test missing required fields
        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resource_id', 'permissions']);

        // Test invalid resource type
        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'invalid',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id,
            'permissions' => ['view' => true]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resource_type']);

        // Test invalid assignable type
        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'invalid',
            'assignable_id' => $this->regularUser->id,
            'permissions' => ['view' => true]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['assignable_type']);
    }

    public function test_non_admin_cannot_assign_permissions()
    {
        $this->actingAs($this->regularUser);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id,
            'permissions' => ['view' => true]
        ]);

        $response->assertStatus(403);
    }

    public function test_effective_permissions_combines_all_sources()
    {
        $this->actingAs($this->adminUser);

        // Assign user to position
        $this->regularUser->positions()->attach($this->position->id, ['is_primary' => true]);

        // Direct user permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id,
            'permissions' => json_encode(['view' => true, 'download' => true]),
            'assigned_by' => $this->adminUser->id
        ]);

        // Position permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\Position',
            'assignable_id' => $this->position->id,
            'permissions' => json_encode(['edit' => true, 'upload' => true]),
            'assigned_by' => $this->adminUser->id
        ]);

        // Department permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => 'App\Models\Department',
            'assignable_id' => $this->department->id,
            'permissions' => json_encode(['delete' => true]),
            'assigned_by' => $this->adminUser->id
        ]);

        $effectivePermissions = $this->permissionService->getEffectivePermissions($this->regularUser, $this->folder);

        $this->assertTrue($effectivePermissions['view']);
        $this->assertTrue($effectivePermissions['download']);
        $this->assertTrue($effectivePermissions['edit']);
        $this->assertTrue($effectivePermissions['upload']);
        $this->assertTrue($effectivePermissions['delete']);
    }

    public function test_file_permission_assignment()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'file',
            'resource_id' => $this->file->id,
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id,
            'permissions' => [
                'view' => true,
                'download' => true
            ]
        ]);

        $response->assertStatus(200);

        // Verify permission was created for file
        $this->assertDatabaseHas('file_permissions', [
            'file_id' => $this->file->id,
            'assignable_type' => 'App\Models\User',
            'assignable_id' => $this->regularUser->id
        ]);

        // Test permission checking for file
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->file, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->regularUser, $this->file, 'download'));
    }

    public function test_permission_templates()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/files/permissions/assign', [
            'resource_type' => 'folder',
            'resource_id' => $this->folder->id,
            'assignable_type' => 'user',
            'assignable_id' => $this->regularUser->id,
            'permissions' => [
                'view' => true,
                'download' => true,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false
            ],
            'template' => 'viewer'
        ]);

        $response->assertStatus(200);

        // Verify viewer template permissions
        $permission = FilePermission::where('folder_id', $this->folder->id)
            ->where('assignable_type', 'App\Models\User')
            ->where('assignable_id', $this->regularUser->id)
            ->first();

        $this->assertTrue($permission->permissions['view']);
        $this->assertTrue($permission->permissions['download']);
        $this->assertFalse($permission->permissions['edit']);
    }
}
