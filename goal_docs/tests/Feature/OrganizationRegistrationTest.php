<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Folder;
use App\Services\OrganizationSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class OrganizationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_registration_creates_user()
    {
        $response = $this->post('/register', [
            'type' => 'organisation',
            'contact_name' => 'Test Admin',
            'phone' => '+1234567890',
            'contact_email' => 'admin@testorg.com',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
            'type_name' => 'Test Organization Ltd'
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('users', [
            'email' => 'admin@testorg.com',
            'type' => 'organisation',
            'type_name' => 'Test Organization Ltd'
        ]);
    }

    public function test_organization_setup_service_creates_folders()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization Ltd',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(12, $folders->count());
        
        $expectedFolders = [
            'Finance', 'Human Resources', 'Operations', 'IT', 'Sales', 'Marketing',
            'Legal', 'Admin', 'Research & Development', 'Customer Service', 
            'Procurement', 'Quality Assurance'
        ];
        
        foreach ($expectedFolders as $folderName) {
            $this->assertTrue($folders->contains('name', $folderName));
        }
    }

    public function test_family_registration_creates_family_folders()
    {
        $user = User::factory()->create([
            'type' => 'family',
            'type_name' => 'Smith Family',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(7, $folders->count());
        
        $expectedFolders = [
            'Personal', 'Medical', 'Financial', 'Legal', 'Education', 'Photos', 'Important Documents'
        ];
        
        foreach ($expectedFolders as $folderName) {
            $this->assertTrue($folders->contains('name', $folderName));
        }
    }

    public function test_government_registration_creates_government_folders()
    {
        $user = User::factory()->create([
            'type' => 'government',
            'type_name' => 'Test Government Agency',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(6, $folders->count());
        
        $expectedFolders = [
            'Public Records', 'Internal Affairs', 'Legal', 'Budget', 'Human Resources', 'Projects'
        ];
        
        foreach ($expectedFolders as $folderName) {
            $this->assertTrue($folders->contains('name', $folderName));
        }
    }

    public function test_educational_registration_creates_educational_folders()
    {
        $user = User::factory()->create([
            'type' => 'educational_institution',
            'type_name' => 'Test University',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(6, $folders->count());
        
        $expectedFolders = [
            'Academic Affairs', 'Administration', 'Student Records', 'Faculty', 'Research', 'Library'
        ];
        
        foreach ($expectedFolders as $folderName) {
            $this->assertTrue($folders->contains('name', $folderName));
        }
    }

    public function test_social_group_registration_creates_social_folders()
    {
        $user = User::factory()->create([
            'type' => 'social_group',
            'type_name' => 'Test Social Group',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(6, $folders->count());
        
        $expectedFolders = [
            'Projects', 'Members', 'Financial', 'Events', 'Documents', 'Resources'
        ];
        
        foreach ($expectedFolders as $folderName) {
            $this->assertTrue($folders->contains('name', $folderName));
        }
    }

    public function test_individual_user_does_not_get_system_folders()
    {
        $user = User::factory()->create([
            'type' => 'individual',
            'type_name' => 'Individual User',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(0, $folders->count());
    }

    public function test_system_folders_have_correct_properties()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization Ltd',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user);
        
        $folder = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->first();

        $this->assertTrue($folder->is_system_folder);
        $this->assertEquals('organisation', $folder->user_type);
        $this->assertEquals('Test Organization Ltd', $folder->type_name);
        $this->assertEquals($user->id, $folder->created_by);
        $this->assertTrue($folder->is_active);
        $this->assertNotNull($folder->description);
    }

    public function test_multiple_initialization_does_not_duplicate_folders()
    {
        $user = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Test Organization Ltd',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        
        // Run initialization twice
        $organizationSetupService->initializeOrganization($user);
        $organizationSetupService->initializeOrganization($user);
        
        $folders = Folder::where('created_by', $user->id)
            ->where('is_system_folder', true)
            ->get();
            
        // Should still only have 12 folders, not 24
        $this->assertEquals(12, $folders->count());
    }

    public function test_folders_are_organization_scoped()
    {
        $user1 = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Organization 1',
            'email_verified_at' => now()
        ]);

        $user2 = User::factory()->create([
            'type' => 'organisation',
            'type_name' => 'Organization 2',
            'email_verified_at' => now()
        ]);

        $organizationSetupService = new OrganizationSetupService();
        $organizationSetupService->initializeOrganization($user1);
        $organizationSetupService->initializeOrganization($user2);
        
        $folders1 = Folder::where('created_by', $user1->id)
            ->where('is_system_folder', true)
            ->get();
            
        $folders2 = Folder::where('created_by', $user2->id)
            ->where('is_system_folder', true)
            ->get();
            
        $this->assertEquals(12, $folders1->count());
        $this->assertEquals(12, $folders2->count());
        
        // Folders should be scoped to their respective organizations
        foreach ($folders1 as $folder) {
            $this->assertEquals('Organization 1', $folder->type_name);
        }
        
        foreach ($folders2 as $folder) {
            $this->assertEquals('Organization 2', $folder->type_name);
        }
    }
}
