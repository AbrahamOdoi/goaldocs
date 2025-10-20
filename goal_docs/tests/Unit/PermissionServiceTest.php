<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PermissionService;
use App\Models\User;
use App\Models\Folder;
use App\Models\Position;
use App\Models\Department;
use App\Models\FilePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $permissionService;
    protected $user;
    protected $folder;
    protected $position;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionService = new PermissionService();
        
        $this->user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization'
        ]);
        
        $this->folder = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->user->id
        ]);
        
        $this->department = Department::factory()->create([
            'user_type' => 'organisation',
            'type' => 'Test Organization',
            'created_by' => $this->user->id
        ]);
        
        $this->position = Position::factory()->create([
            'department_id' => $this->department->id,
            'created_by' => $this->user->id
        ]);
    }

    public function test_user_has_direct_permission()
    {
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => User::class,
            'assignable_id' => $this->user->id,
            'permissions' => json_encode(['view' => true, 'download' => true]),
            'assigned_by' => $this->user->id
        ]);

        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'download'));
        $this->assertFalse($this->permissionService->userHasPermission($this->user, $this->folder, 'edit'));
    }

    public function test_user_has_permission_from_position()
    {
        $this->user->positions()->attach($this->position->id, ['is_primary' => true]);
        
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Position::class,
            'assignable_id' => $this->position->id,
            'permissions' => json_encode(['view' => true, 'edit' => true]),
            'assigned_by' => $this->user->id
        ]);

        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'edit'));
    }

    public function test_user_has_permission_from_department()
    {
        $this->user->positions()->attach($this->position->id, ['is_primary' => true]);
        
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Department::class,
            'assignable_id' => $this->department->id,
            'permissions' => json_encode(['view' => true, 'upload' => true]),
            'assigned_by' => $this->user->id
        ]);

        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'view'));
        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'upload'));
    }

    public function test_permission_conflict_resolution_most_permissive()
    {
        $position2 = Position::factory()->create([
            'department_id' => $this->department->id,
            'created_by' => $this->user->id
        ]);
        
        $this->user->positions()->attach([
            $this->position->id => ['is_primary' => true],
            $position2->id => ['is_primary' => false]
        ]);
        
        // Position 1: Can edit
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Position::class,
            'assignable_id' => $this->position->id,
            'permissions' => json_encode(['edit' => true]),
            'assigned_by' => $this->user->id
        ]);
        
        // Position 2: Cannot edit
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Position::class,
            'assignable_id' => $position2->id,
            'permissions' => json_encode(['edit' => false]),
            'assigned_by' => $this->user->id
        ]);

        // Should use most permissive rule
        $this->assertTrue($this->permissionService->userHasPermission($this->user, $this->folder, 'edit'));
    }

    public function test_effective_permissions_combines_all_sources()
    {
        $this->user->positions()->attach($this->position->id, ['is_primary' => true]);
        
        // Direct user permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => User::class,
            'assignable_id' => $this->user->id,
            'permissions' => json_encode(['view' => true, 'download' => true]),
            'assigned_by' => $this->user->id
        ]);
        
        // Position permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Position::class,
            'assignable_id' => $this->position->id,
            'permissions' => json_encode(['edit' => true, 'upload' => true]),
            'assigned_by' => $this->user->id
        ]);
        
        // Department permission
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => Department::class,
            'assignable_id' => $this->department->id,
            'permissions' => json_encode(['delete' => true]),
            'assigned_by' => $this->user->id
        ]);

        $effectivePermissions = $this->permissionService->getEffectivePermissions($this->user, $this->folder);
        
        $this->assertTrue($effectivePermissions['view']);
        $this->assertTrue($effectivePermissions['download']);
        $this->assertTrue($effectivePermissions['edit']);
        $this->assertTrue($effectivePermissions['upload']);
        $this->assertTrue($effectivePermissions['delete']);
    }

    public function test_admin_user_can_manage_permissions()
    {
        $adminUser = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization',
            'is_admin' => true
        ]);

        $this->assertTrue($this->permissionService->canManagePermissions($adminUser, $this->folder));
    }

    public function test_user_with_manage_permission_can_manage()
    {
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => User::class,
            'assignable_id' => $this->user->id,
            'permissions' => json_encode(['manage' => true]),
            'assigned_by' => $this->user->id
        ]);

        $this->assertTrue($this->permissionService->canManagePermissions($this->user, $this->folder));
    }

    public function test_bulk_assign_permissions()
    {
        $folder2 = Folder::factory()->create([
            'user_type' => 'organisation',
            'type_name' => 'Test Organization',
            'created_by' => $this->user->id
        ]);

        $assignableData = [
            [
                'type' => 'user',
                'id' => $this->user->id
            ]
        ];

        $resourceIds = [$this->folder->id, $folder2->id];
        $permissions = ['view' => true, 'download' => true];

        $result = $this->permissionService->bulkAssignPermissions(
            $assignableData,
            $resourceIds,
            $permissions,
            'folder'
        );

        $this->assertTrue($result);

        // Verify permissions were created
        $permissions1 = FilePermission::where('folder_id', $this->folder->id)
            ->where('assignable_type', User::class)
            ->where('assignable_id', $this->user->id)
            ->first();
            
        $permissions2 = FilePermission::where('folder_id', $folder2->id)
            ->where('assignable_type', User::class)
            ->where('assignable_id', $this->user->id)
            ->first();

        $this->assertNotNull($permissions1);
        $this->assertNotNull($permissions2);
        $this->assertTrue($permissions1->permissions['view']);
        $this->assertTrue($permissions2->permissions['view']);
    }

    public function test_permission_templates()
    {
        $templates = $this->permissionService->getPermissionTemplates();
        
        $this->assertIsArray($templates);
        $this->assertArrayHasKey('viewer', $templates);
        $this->assertArrayHasKey('editor', $templates);
        $this->assertArrayHasKey('manager', $templates);
        $this->assertArrayHasKey('admin', $templates);
        
        $this->assertTrue($templates['viewer']['view']);
        $this->assertFalse($templates['viewer']['edit']);
        
        $this->assertTrue($templates['editor']['view']);
        $this->assertTrue($templates['editor']['edit']);
        $this->assertFalse($templates['editor']['manage']);
        
        $this->assertTrue($templates['manager']['view']);
        $this->assertTrue($templates['manager']['edit']);
        $this->assertTrue($templates['manager']['manage']);
        
        $this->assertTrue($templates['admin']['view']);
        $this->assertTrue($templates['admin']['edit']);
        $this->assertTrue($templates['admin']['manage']);
    }

    public function test_access_report_generation()
    {
        FilePermission::create([
            'folder_id' => $this->folder->id,
            'assignable_type' => User::class,
            'assignable_id' => $this->user->id,
            'permissions' => json_encode(['view' => true, 'edit' => true]),
            'assigned_by' => $this->user->id
        ]);

        $report = $this->permissionService->getAccessReport($this->folder);
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('direct_permissions', $report);
        $this->assertArrayHasKey('inherited_permissions', $report);
        $this->assertArrayHasKey('summary', $report);
        
        $this->assertCount(1, $report['direct_permissions']);
        $this->assertEquals(User::class, $report['direct_permissions'][0]['assignable_type']);
        $this->assertEquals($this->user->id, $report['direct_permissions'][0]['assignable_id']);
    }
}
