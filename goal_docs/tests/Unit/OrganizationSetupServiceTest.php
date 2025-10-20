<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OrganizationSetupService;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationSetupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $organizationSetupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationSetupService = new OrganizationSetupService();
    }

    public function test_organization_initialization_creates_folders()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        $this->assertEquals(12, $folders->count());
        $this->assertTrue($folders->contains('name', 'Finance'));
        $this->assertTrue($folders->contains('name', 'Human Resources'));
        $this->assertTrue($folders->contains('name', 'Operations'));
        $this->assertTrue($folders->contains('name', 'IT'));
        $this->assertTrue($folders->contains('name', 'Sales'));
        $this->assertTrue($folders->contains('name', 'Marketing'));
        $this->assertTrue($folders->contains('name', 'Legal'));
        $this->assertTrue($folders->contains('name', 'Admin'));
        $this->assertTrue($folders->contains('name', 'Research & Development'));
        $this->assertTrue($folders->contains('name', 'Customer Service'));
        $this->assertTrue($folders->contains('name', 'Procurement'));
        $this->assertTrue($folders->contains('name', 'Quality Assurance'));
    }

    public function test_family_initialization_creates_family_folders()
    {
        $user = User::factory()->create([
            'type' => 'family',
            'type_name' => 'Test Family'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        $this->assertEquals(7, $folders->count());
        $this->assertTrue($folders->contains('name', 'Personal'));
        $this->assertTrue($folders->contains('name', 'Medical'));
        $this->assertTrue($folders->contains('name', 'Financial'));
        $this->assertTrue($folders->contains('name', 'Legal'));
        $this->assertTrue($folders->contains('name', 'Education'));
        $this->assertTrue($folders->contains('name', 'Photos'));
        $this->assertTrue($folders->contains('name', 'Important Documents'));
    }

    public function test_government_initialization_creates_government_folders()
    {
        $user = User::factory()->create([
            'type' => 'government',
            'type_name' => 'Test Government Agency'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        $this->assertEquals(6, $folders->count());
        $this->assertTrue($folders->contains('name', 'Public Records'));
        $this->assertTrue($folders->contains('name', 'Internal Affairs'));
        $this->assertTrue($folders->contains('name', 'Legal'));
        $this->assertTrue($folders->contains('name', 'Budget'));
        $this->assertTrue($folders->contains('name', 'Human Resources'));
        $this->assertTrue($folders->contains('name', 'Projects'));
    }

    public function test_educational_initialization_creates_educational_folders()
    {
        $user = User::factory()->create([
            'type' => 'educational_institution',
            'type_name' => 'Test University'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        $this->assertEquals(6, $folders->count());
        $this->assertTrue($folders->contains('name', 'Academic Affairs'));
        $this->assertTrue($folders->contains('name', 'Administration'));
        $this->assertTrue($folders->contains('name', 'Student Records'));
        $this->assertTrue($folders->contains('name', 'Faculty'));
        $this->assertTrue($folders->contains('name', 'Research'));
        $this->assertTrue($folders->contains('name', 'Library'));
    }

    public function test_system_folders_have_correct_properties()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folder = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->first();

        $this->assertTrue($folder->is_system_folder);
        $this->assertEquals('organisation', $folder->user_type);
        $this->assertEquals('Test Organization', $folder->type_name);
        $this->assertEquals($user->id, $folder->created_by);
        $this->assertTrue($folder->is_active);
    }

    public function test_individual_user_type_does_not_create_folders()
    {
        $user = User::factory()->create([
            'type' => 'individual',
            'type_name' => 'Individual User'
        ]);

        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        $this->assertEquals(0, $folders->count());
    }

    public function test_initialization_does_not_duplicate_folders()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization'
        ]);

        // Run initialization twice
        $this->organizationSetupService->initializeOrganization($user);
        $this->organizationSetupService->initializeOrganization($user);

        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();

        // Should still only have 12 folders, not 24
        $this->assertEquals(12, $folders->count());
    }
}
