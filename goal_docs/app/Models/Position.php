<?php

/**
 * Position Model - Job/Role Management for GoalDocs Enterprise System
 * 
 * This model represents positions/jobs/roles within departments in the GoalDocs system,
 * providing the organizational structure that connects users to departments and
 * enables hierarchical permission management.
 * 
 * Key Features:
 * - Position management within departments
 * - User assignment to positions with date tracking
 * - Primary position designation for users
 * - Permission inheritance through organizational hierarchy
 * - Active/inactive status management
 * - File permission assignment capabilities
 * 
 * Position Types:
 * - Jobs within corporate departments
 * - Roles within family structures
 * - Positions within government agencies
 * - Roles within educational institutions
 * - Positions within social/professional groups
 * 
 * @package App\Models
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',         // Position/job/role name
        'description',  // Optional description of the position
        'department_id', // Parent department ID
        'level',        // Position level/hierarchy
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
     * Get the department this position belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all users assigned to this position.
     * 
     * Returns users with their assignment details including start/end dates,
     * primary position status, and active status.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_positions')
                    ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get all active users assigned to this position.
     * 
     * Returns only users with active assignments to this position.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Scope for filtering active positions only.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering positions by department.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId The department ID to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Get file permissions assigned to this position.
     * 
     * Returns all file permissions that are assigned to this position
     * using polymorphic relationships for flexible permission assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function filePermissions()
    {
        return $this->morphMany(FilePermission::class, 'assignable');
    }
} 