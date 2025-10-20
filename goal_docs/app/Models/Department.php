<?php

/**
 * Department Model - Organizational Structure Management for GoalDocs Enterprise System
 * 
 * This model represents the organizational structure within the GoalDocs system,
 * providing department/role/agency management across different user types and
 * supporting the hierarchical permission system.
 * 
 * Key Features:
 * - Multi-user type support with dynamic terminology
 * - Position management within departments
 * - Permission inheritance through organizational hierarchy
 * - Color coding for visual organization
 * - Active/inactive status management
 * - File permission assignment capabilities
 * 
 * User Type Terminology:
 * - Organisation: "Department"
 * - Family: "Role" 
 * - Government: "Agency"
 * - Social Group: "Group"
 * - Professional Group: "Division"
 * - Educational Institution: "Department"
 * - Non-Profit: "Department"
 * - Individual: "Category"
 * 
 * @package App\Models
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',         // Department/role/agency name
        'description',  // Optional description of the department
        'type',         // Department type identifier
        'user_type',    // User type context (individual, organisation, etc.)
        'color',        // Color code for visual organization
        'is_active',    // Active status flag
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean', // Cast to boolean for status checks
    ];

    /**
     * Get all positions within this department.
     * 
     * Returns all positions/jobs/roles that exist within this department
     * for organizational structure management.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get all active positions within this department.
     * 
     * Returns only active positions, excluding inactive or deleted positions.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activePositions()
    {
        return $this->positions()->where('is_active', true);
    }

    /**
     * Get all users assigned to this department through positions.
     * 
     * Uses a many-through relationship to access users via their positions
     * within this department.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, Position::class);
    }

    /**
     * Get the display name for this department based on user type.
     * 
     * Returns the appropriate terminology for the department based on the
     * user type context (e.g., "Department" for organizations, "Role" for families).
     * 
     * @return string Appropriate display name for the user type
     */
    public function getDisplayNameAttribute()
    {
        $typeLabels = [
            'organisation' => 'Department',
            'family' => 'Role',
            'government' => 'Agency',
            'social_group' => 'Group',
            'professional_group' => 'Division',
            'educational_institution' => 'Department',
            'non_profit' => 'Department',
            'individual' => 'Category',
        ];

        return $typeLabels[$this->user_type] ?? 'Department';
    }

    /**
     * Scope for filtering departments by user type.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userType The user type to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for filtering active departments only.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get file permissions assigned to this department.
     * 
     * Returns all file permissions that are assigned to this department
     * using polymorphic relationships for flexible permission assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function filePermissions()
    {
        return $this->morphMany(FilePermission::class, 'assignable');
    }
} 