<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OrganizationSetupService - Handles organization initialization and folder seeding
 * 
 * This service manages the automatic setup of organizational structures when
 * a new organization signs up, including default folder creation and department
 * structure initialization.
 */
class OrganizationSetupService
{
    /**
     * Initialize organization structure for a new user
     */
    public function initializeOrganization(User $user): bool
    {
        if ($user->type === 'individual') {
            return $this->initializeIndividualStructure($user);
        }

        return $this->initializeOrganizationalStructure($user);
    }

    /**
     * Initialize structure for individual users
     */
    private function initializeIndividualStructure(User $user): bool
    {
        try {
            DB::beginTransaction();

            $folders = [
                ['name' => 'Personal Documents', 'description' => 'Personal files and documents'],
                ['name' => 'Work', 'description' => 'Work-related documents'],
                ['name' => 'Financial', 'description' => 'Financial documents and records'],
                ['name' => 'Medical', 'description' => 'Medical records and health documents'],
                ['name' => 'Photos', 'description' => 'Personal photos and images'],
                ['name' => 'Important', 'description' => 'Important documents and certificates'],
            ];

            $this->createFolders($user, $folders);

            DB::commit();
            Log::info("Individual structure initialized for user: {$user->email}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to initialize individual structure for user {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize structure for organizational users
     */
    private function initializeOrganizationalStructure(User $user): bool
    {
        try {
            DB::beginTransaction();

            // Create default folders based on user type
            $folders = $this->getDefaultFolders($user->type);
            $this->createFolders($user, $folders);

            // Create default departments and positions
            $this->createDefaultDepartments($user);

            DB::commit();
            Log::info("Organizational structure initialized for user: {$user->email} (Type: {$user->type})");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to initialize organizational structure for user {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get default folders based on user type
     */
    private function getDefaultFolders(string $userType): array
    {
        $folderTemplates = [
            'organisation' => [
                ['name' => 'Finance', 'description' => 'Financial documents, budgets, and accounting records'],
                ['name' => 'Human Resources', 'description' => 'HR documents, policies, and employee records'],
                ['name' => 'Operations', 'description' => 'Operational documents and procedures'],
                ['name' => 'IT', 'description' => 'IT documentation, policies, and technical resources'],
                ['name' => 'Sales', 'description' => 'Sales documents, contracts, and customer records'],
                ['name' => 'Marketing', 'description' => 'Marketing materials, campaigns, and brand assets'],
                ['name' => 'Legal', 'description' => 'Legal documents, contracts, and compliance records'],
                ['name' => 'Administration', 'description' => 'Administrative documents and general records'],
                ['name' => 'R&D', 'description' => 'Research and development documents and projects'],
                ['name' => 'Customer Service', 'description' => 'Customer service documents and support materials'],
                ['name' => 'Procurement', 'description' => 'Procurement documents, vendor contracts, and purchasing records'],
                ['name' => 'Quality Assurance', 'description' => 'QA documents, standards, and quality control records'],
            ],
            'family' => [
                ['name' => 'Personal', 'description' => 'Personal documents and files'],
                ['name' => 'Medical', 'description' => 'Medical records and health documents'],
                ['name' => 'Financial', 'description' => 'Family financial documents and records'],
                ['name' => 'Legal', 'description' => 'Legal documents, wills, and important papers'],
                ['name' => 'Education', 'description' => 'Educational documents and school records'],
                ['name' => 'Photos', 'description' => 'Family photos and memories'],
                ['name' => 'Important Documents', 'description' => 'Important certificates and vital documents'],
            ],
            'government' => [
                ['name' => 'Public Records', 'description' => 'Public records and citizen documents'],
                ['name' => 'Internal Affairs', 'description' => 'Internal government affairs and operations'],
                ['name' => 'Legal', 'description' => 'Legal documents and regulatory compliance'],
                ['name' => 'Budget', 'description' => 'Budget documents and financial planning'],
                ['name' => 'Human Resources', 'description' => 'Government HR and personnel records'],
                ['name' => 'Projects', 'description' => 'Government projects and initiatives'],
            ],
            'educational_institution' => [
                ['name' => 'Academic Affairs', 'description' => 'Academic programs and curriculum documents'],
                ['name' => 'Administration', 'description' => 'Administrative documents and policies'],
                ['name' => 'Student Records', 'description' => 'Student information and academic records'],
                ['name' => 'Faculty', 'description' => 'Faculty documents and personnel records'],
                ['name' => 'Research', 'description' => 'Research projects and academic publications'],
                ['name' => 'Library', 'description' => 'Library resources and documentation'],
            ],
            'social_group' => [
                ['name' => 'Projects', 'description' => 'Group projects and initiatives'],
                ['name' => 'Members', 'description' => 'Member information and records'],
                ['name' => 'Financial', 'description' => 'Group financial documents and budgets'],
                ['name' => 'Events', 'description' => 'Event planning and documentation'],
                ['name' => 'Documents', 'description' => 'General group documents'],
                ['name' => 'Resources', 'description' => 'Group resources and materials'],
            ],
            'professional_group' => [
                ['name' => 'Projects', 'description' => 'Professional projects and initiatives'],
                ['name' => 'Members', 'description' => 'Member information and professional records'],
                ['name' => 'Financial', 'description' => 'Professional group financial documents'],
                ['name' => 'Events', 'description' => 'Professional events and conferences'],
                ['name' => 'Documents', 'description' => 'Professional documents and standards'],
                ['name' => 'Resources', 'description' => 'Professional resources and materials'],
            ],
            'non_profit' => [
                ['name' => 'Projects', 'description' => 'Non-profit projects and programs'],
                ['name' => 'Members', 'description' => 'Member and volunteer information'],
                ['name' => 'Financial', 'description' => 'Non-profit financial documents and budgets'],
                ['name' => 'Events', 'description' => 'Fundraising and community events'],
                ['name' => 'Documents', 'description' => 'Non-profit documents and policies'],
                ['name' => 'Resources', 'description' => 'Non-profit resources and materials'],
            ],
        ];

        return $folderTemplates[$userType] ?? $folderTemplates['organisation'];
    }

    /**
     * Create folders for the user
     */
    private function createFolders(User $user, array $folders): void
    {
        foreach ($folders as $folderData) {
            Folder::create([
                'name' => $folderData['name'],
                'description' => $folderData['description'],
                'user_type' => $user->type,
                'type_name' => $user->type_name,
                'created_by' => $user->id,
                'is_active' => true,
                'is_system_folder' => true, // Mark as system-seeded folder
            ]);
        }
    }

    /**
     * Create default departments and positions for organizational users
     */
    private function createDefaultDepartments(User $user): void
    {
        $departments = $this->getDefaultDepartments($user->type);

        foreach ($departments as $deptData) {
            $department = Department::create([
                'name' => $deptData['name'],
                'description' => $deptData['description'],
                'type' => $user->type_name,
                'user_type' => $user->type,
                'color' => $deptData['color'],
                'is_active' => true,
            ]);

            // Create default positions for this department
            if (isset($deptData['positions'])) {
                $this->createDefaultPositions($department, $deptData['positions']);
            }
        }
    }

    /**
     * Get default departments based on user type
     */
    private function getDefaultDepartments(string $userType): array
    {
        $departmentTemplates = [
            'organisation' => [
                [
                    'name' => 'Finance',
                    'description' => 'Financial management and accounting',
                    'color' => '#28a745',
                    'positions' => [
                        ['name' => 'CFO', 'level' => 'executive'],
                        ['name' => 'Finance Manager', 'level' => 'manager'],
                        ['name' => 'Accountant', 'level' => 'mid'],
                        ['name' => 'Financial Analyst', 'level' => 'mid'],
                    ]
                ],
                [
                    'name' => 'Human Resources',
                    'description' => 'Human resources and personnel management',
                    'color' => '#17a2b8',
                    'positions' => [
                        ['name' => 'HR Director', 'level' => 'executive'],
                        ['name' => 'HR Manager', 'level' => 'manager'],
                        ['name' => 'HR Specialist', 'level' => 'mid'],
                        ['name' => 'HR Assistant', 'level' => 'entry'],
                    ]
                ],
                [
                    'name' => 'Operations',
                    'description' => 'Operations and process management',
                    'color' => '#6c757d',
                    'positions' => [
                        ['name' => 'COO', 'level' => 'executive'],
                        ['name' => 'Operations Manager', 'level' => 'manager'],
                        ['name' => 'Operations Specialist', 'level' => 'mid'],
                    ]
                ],
                [
                    'name' => 'IT',
                    'description' => 'Information technology and systems',
                    'color' => '#007bff',
                    'positions' => [
                        ['name' => 'CTO', 'level' => 'executive'],
                        ['name' => 'IT Manager', 'level' => 'manager'],
                        ['name' => 'Senior Developer', 'level' => 'senior'],
                        ['name' => 'Developer', 'level' => 'mid'],
                        ['name' => 'System Administrator', 'level' => 'mid'],
                    ]
                ],
                [
                    'name' => 'Sales',
                    'description' => 'Sales and business development',
                    'color' => '#fd7e14',
                    'positions' => [
                        ['name' => 'VP of Sales', 'level' => 'executive'],
                        ['name' => 'Sales Manager', 'level' => 'manager'],
                        ['name' => 'Senior Sales Rep', 'level' => 'senior'],
                        ['name' => 'Sales Representative', 'level' => 'entry'],
                    ]
                ],
                [
                    'name' => 'Marketing',
                    'description' => 'Marketing and brand management',
                    'color' => '#e83e8c',
                    'positions' => [
                        ['name' => 'Marketing Director', 'level' => 'executive'],
                        ['name' => 'Marketing Manager', 'level' => 'manager'],
                        ['name' => 'Marketing Specialist', 'level' => 'mid'],
                        ['name' => 'Marketing Assistant', 'level' => 'entry'],
                    ]
                ],
            ],
            'family' => [
                [
                    'name' => 'Parent',
                    'description' => 'Primary caregiver and decision maker',
                    'color' => '#ff9ff3',
                    'positions' => [
                        ['name' => 'Primary Parent', 'level' => 'manager'],
                        ['name' => 'Co-Parent', 'level' => 'senior'],
                    ]
                ],
                [
                    'name' => 'Child',
                    'description' => 'Family member under care',
                    'color' => '#54a0ff',
                    'positions' => [
                        ['name' => 'Infant', 'level' => 'entry'],
                        ['name' => 'Toddler', 'level' => 'entry'],
                        ['name' => 'School Age', 'level' => 'mid'],
                        ['name' => 'Teenager', 'level' => 'senior'],
                    ]
                ],
                [
                    'name' => 'Guardian',
                    'description' => 'Legal guardian or caretaker',
                    'color' => '#5f27cd',
                    'positions' => [
                        ['name' => 'Legal Guardian', 'level' => 'manager'],
                        ['name' => 'Temporary Guardian', 'level' => 'senior'],
                    ]
                ],
            ],
            'government' => [
                [
                    'name' => 'Administration',
                    'description' => 'Administrative and operational support',
                    'color' => '#00d2d3',
                    'positions' => [
                        ['name' => 'Administrative Manager', 'level' => 'manager'],
                        ['name' => 'Administrative Officer', 'level' => 'mid'],
                        ['name' => 'Administrative Assistant', 'level' => 'entry'],
                    ]
                ],
                [
                    'name' => 'Public Services',
                    'description' => 'Direct service delivery to citizens',
                    'color' => '#ff6348',
                    'positions' => [
                        ['name' => 'Service Manager', 'level' => 'manager'],
                        ['name' => 'Senior Service Officer', 'level' => 'senior'],
                        ['name' => 'Service Officer', 'level' => 'entry'],
                    ]
                ],
            ],
        ];

        return $departmentTemplates[$userType] ?? $departmentTemplates['organisation'];
    }

    /**
     * Create default positions for a department
     */
    private function createDefaultPositions(Department $department, array $positions): void
    {
        foreach ($positions as $positionData) {
            Position::create([
                'name' => $positionData['name'],
                'description' => 'Default position in ' . $department->name . ' department',
                'department_id' => $department->id,
                'level' => $positionData['level'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Check if organization has been initialized
     */
    public function isInitialized(User $user): bool
    {
        if ($user->type === 'individual') {
            return Folder::where('user_type', $user->type)
                ->where('created_by', $user->id)
                ->exists();
        }

        return Department::where('user_type', $user->type)
            ->where('type', $user->type_name)
            ->exists();
    }

    /**
     * Re-initialize organization structure (for admin use)
     */
    public function reinitializeOrganization(User $user): bool
    {
        // Only allow re-initialization for admin users
        if (!$user->is_admin) {
            return false;
        }

        return $this->initializeOrganization($user);
    }
}
