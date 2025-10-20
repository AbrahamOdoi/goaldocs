<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class PositionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $department;
    protected $position1;
    protected $position2;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        $this->position1 = Position::factory()->create([
            'name' => 'Senior Developer',
            'department_id' => $this->department->id,
            'created_by' => $this->adminUser->id
        ]);

        $this->position2 = Position::factory()->create([
            'name' => 'Marketing Manager',
            'department_id' => $this->department->id,
            'created_by' => $this->adminUser->id
        ]);
    }

    public function test_admin_can_assign_multiple_positions()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id,
            'positions' => [
                [
                    'position_id' => $this->position1->id,
                    'is_primary' => true,
                    'start_date' => '2024-01-01'
                ],
                [
                    'position_id' => $this->position2->id,
                    'is_primary' => false,
                    'start_date' => '2024-01-01'
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify positions were assigned
        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position1->id,
            'is_primary' => true
        ]);

        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position2->id,
            'is_primary' => false
        ]);
    }

    public function test_non_admin_cannot_assign_positions()
    {
        $this->actingAs($this->regularUser);

        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id,
            'positions' => [
                [
                    'position_id' => $this->position1->id,
                    'is_primary' => true
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    public function test_set_primary_position()
    {
        $this->actingAs($this->adminUser);

        // First assign multiple positions
        $this->regularUser->positions()->attach([
            $this->position1->id => ['is_primary' => true, 'is_active' => true],
            $this->position2->id => ['is_primary' => false, 'is_active' => true]
        ]);

        // Change primary position
        $response = $this->post('/hierarchy/positions/set-primary', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position2->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify primary position changed
        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position1->id,
            'is_primary' => false
        ]);

        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position2->id,
            'is_primary' => true
        ]);
    }

    public function test_get_user_positions()
    {
        $this->actingAs($this->adminUser);

        // Assign positions
        $this->regularUser->positions()->attach([
            $this->position1->id => ['is_primary' => true, 'is_active' => true],
            $this->position2->id => ['is_primary' => false, 'is_active' => true]
        ]);

        $response = $this->get("/hierarchy/user/{$this->regularUser->id}/positions");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'positions' => [
                '*' => [
                    'id',
                    'name',
                    'department_name',
                    'is_primary',
                    'start_date',
                    'is_active'
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['positions']);
        
        $primaryPosition = collect($data['positions'])->firstWhere('is_primary', true);
        $this->assertEquals('Senior Developer', $primaryPosition['name']);
    }

    public function test_get_position_history()
    {
        $this->actingAs($this->adminUser);

        // Assign and then deactivate a position
        $this->regularUser->positions()->attach([
            $this->position1->id => [
                'is_primary' => true, 
                'is_active' => false,
                'start_date' => '2024-01-01',
                'end_date' => '2024-06-01'
            ],
            $this->position2->id => [
                'is_primary' => true, 
                'is_active' => true,
                'start_date' => '2024-06-01'
            ]
        ]);

        $response = $this->get("/hierarchy/user/{$this->regularUser->id}/position-history");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'history' => [
                '*' => [
                    'id',
                    'name',
                    'department_name',
                    'is_primary',
                    'start_date',
                    'end_date',
                    'is_active'
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['history']);
        
        $activePosition = collect($data['history'])->firstWhere('is_active', true);
        $inactivePosition = collect($data['history'])->firstWhere('is_active', false);
        
        $this->assertEquals('Marketing Manager', $activePosition['name']);
        $this->assertEquals('Senior Developer', $inactivePosition['name']);
        $this->assertNotNull($inactivePosition['end_date']);
    }

    public function test_validation_for_multiple_position_assignment()
    {
        $this->actingAs($this->adminUser);

        // Test missing user_id
        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'positions' => [
                ['position_id' => $this->position1->id, 'is_primary' => true]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);

        // Test missing positions array
        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['positions']);

        // Test invalid position_id
        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id,
            'positions' => [
                ['position_id' => 999, 'is_primary' => true]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['positions.0.position_id']);
    }

    public function test_cannot_assign_positions_from_different_organization()
    {
        $this->actingAs($this->adminUser);

        // Create position from different organization
        $otherDepartment = Department::factory()->create([
            'user_type' => 'organisation',
            'type' => 'Other Organization',
            'created_by' => $this->adminUser->id
        ]);

        $otherPosition = Position::factory()->create([
            'department_id' => $otherDepartment->id,
            'created_by' => $this->adminUser->id
        ]);

        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id,
            'positions' => [
                ['position_id' => $otherPosition->id, 'is_primary' => true]
            ]
        ]);

        $response->assertStatus(404); // Position not found for this organization
    }

    public function test_automatic_primary_assignment_when_none_specified()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/hierarchy/positions/assign-multiple', [
            'user_id' => $this->regularUser->id,
            'positions' => [
                [
                    'position_id' => $this->position1->id,
                    'is_primary' => false
                ],
                [
                    'position_id' => $this->position2->id,
                    'is_primary' => false
                ]
            ]
        ]);

        $response->assertStatus(200);

        // First position should automatically become primary
        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position1->id,
            'is_primary' => true
        ]);

        $this->assertDatabaseHas('user_positions', [
            'user_id' => $this->regularUser->id,
            'position_id' => $this->position2->id,
            'is_primary' => false
        ]);
    }

    public function test_position_assignment_ui_access()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get("/users/{$this->regularUser->id}/assign-positions");

        $response->assertStatus(200);
        $response->assertViewIs('users.assign-positions');
        $response->assertViewHas(['user', 'availablePositions']);
    }

    public function test_non_admin_cannot_access_position_assignment_ui()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get("/users/{$this->regularUser->id}/assign-positions");

        $response->assertStatus(403);
    }
}
