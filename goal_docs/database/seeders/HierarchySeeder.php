<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class HierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample departments for different user types
        $departments = [
            // Organisation departments
            [
                'name' => 'Engineering',
                'description' => 'Software development and technical operations',
                'type' => 'department',
                'user_type' => 'organisation',
                'color' => '#696cff',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Brand management and customer acquisition',
                'type' => 'department',
                'user_type' => 'organisation',
                'color' => '#ff6b6b',
            ],
            [
                'name' => 'Sales',
                'description' => 'Revenue generation and customer relationships',
                'type' => 'department',
                'user_type' => 'organisation',
                'color' => '#4ecdc4',
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Employee management and organizational development',
                'type' => 'department',
                'user_type' => 'organisation',
                'color' => '#45b7d1',
            ],
            
            // Family roles
            [
                'name' => 'Parent',
                'description' => 'Primary caregiver and decision maker',
                'type' => 'role',
                'user_type' => 'family',
                'color' => '#ff9ff3',
            ],
            [
                'name' => 'Child',
                'description' => 'Family member under care',
                'type' => 'role',
                'user_type' => 'family',
                'color' => '#54a0ff',
            ],
            [
                'name' => 'Guardian',
                'description' => 'Legal guardian or caretaker',
                'type' => 'role',
                'user_type' => 'family',
                'color' => '#5f27cd',
            ],
            
            // Government agencies
            [
                'name' => 'Administration',
                'description' => 'Administrative and operational support',
                'type' => 'agency',
                'user_type' => 'government',
                'color' => '#00d2d3',
            ],
            [
                'name' => 'Public Services',
                'description' => 'Direct service delivery to citizens',
                'type' => 'agency',
                'user_type' => 'government',
                'color' => '#ff6348',
            ],
        ];

        foreach ($departments as $deptData) {
            $department = Department::create($deptData);
            
            // Add sample positions based on department type
            if ($deptData['user_type'] === 'organisation') {
                $this->addOrganisationPositions($department);
            } elseif ($deptData['user_type'] === 'family') {
                $this->addFamilyPositions($department);
            } elseif ($deptData['user_type'] === 'government') {
                $this->addGovernmentPositions($department);
            }
        }
    }

    private function addOrganisationPositions($department)
    {
        $positions = [];
        
        switch ($department->name) {
            case 'Engineering':
                $positions = [
                    ['name' => 'Software Engineer', 'level' => 'entry'],
                    ['name' => 'Senior Developer', 'level' => 'senior'],
                    ['name' => 'Tech Lead', 'level' => 'lead'],
                    ['name' => 'Engineering Manager', 'level' => 'manager'],
                    ['name' => 'CTO', 'level' => 'executive'],
                ];
                break;
            case 'Marketing':
                $positions = [
                    ['name' => 'Marketing Assistant', 'level' => 'entry'],
                    ['name' => 'Marketing Specialist', 'level' => 'mid'],
                    ['name' => 'Marketing Manager', 'level' => 'manager'],
                    ['name' => 'Marketing Director', 'level' => 'executive'],
                ];
                break;
            case 'Sales':
                $positions = [
                    ['name' => 'Sales Representative', 'level' => 'entry'],
                    ['name' => 'Senior Sales Rep', 'level' => 'senior'],
                    ['name' => 'Sales Manager', 'level' => 'manager'],
                    ['name' => 'VP of Sales', 'level' => 'executive'],
                ];
                break;
            case 'Human Resources':
                $positions = [
                    ['name' => 'HR Assistant', 'level' => 'entry'],
                    ['name' => 'HR Specialist', 'level' => 'mid'],
                    ['name' => 'HR Manager', 'level' => 'manager'],
                    ['name' => 'HR Director', 'level' => 'executive'],
                ];
                break;
        }
        
        foreach ($positions as $posData) {
            Position::create([
                'name' => $posData['name'],
                'description' => 'Position in ' . $department->name . ' department',
                'department_id' => $department->id,
                'level' => $posData['level'],
            ]);
        }
    }

    private function addFamilyPositions($department)
    {
        $positions = [];
        
        switch ($department->name) {
            case 'Parent':
                $positions = [
                    ['name' => 'Primary Parent', 'level' => 'manager'],
                    ['name' => 'Co-Parent', 'level' => 'senior'],
                ];
                break;
            case 'Child':
                $positions = [
                    ['name' => 'Infant', 'level' => 'entry'],
                    ['name' => 'Toddler', 'level' => 'entry'],
                    ['name' => 'School Age', 'level' => 'mid'],
                    ['name' => 'Teenager', 'level' => 'senior'],
                ];
                break;
            case 'Guardian':
                $positions = [
                    ['name' => 'Legal Guardian', 'level' => 'manager'],
                    ['name' => 'Temporary Guardian', 'level' => 'senior'],
                ];
                break;
        }
        
        foreach ($positions as $posData) {
            Position::create([
                'name' => $posData['name'],
                'description' => 'Role in ' . $department->name . ' category',
                'department_id' => $department->id,
                'level' => $posData['level'],
            ]);
        }
    }

    private function addGovernmentPositions($department)
    {
        $positions = [];
        
        switch ($department->name) {
            case 'Administration':
                $positions = [
                    ['name' => 'Administrative Assistant', 'level' => 'entry'],
                    ['name' => 'Administrative Officer', 'level' => 'mid'],
                    ['name' => 'Administrative Manager', 'level' => 'manager'],
                ];
                break;
            case 'Public Services':
                $positions = [
                    ['name' => 'Service Officer', 'level' => 'entry'],
                    ['name' => 'Senior Service Officer', 'level' => 'senior'],
                    ['name' => 'Service Manager', 'level' => 'manager'],
                ];
                break;
        }
        
        foreach ($positions as $posData) {
            Position::create([
                'name' => $posData['name'],
                'description' => 'Position in ' . $department->name . ' agency',
                'department_id' => $department->id,
                'level' => $posData['level'],
            ]);
        }
    }
} 