<?php

/**
 * User Model - Central User Management for GoalDocs Enterprise Document Management System
 * 
 * This model represents the core user entity in the GoalDocs system, handling authentication,
 * authorization, and user-related operations across all supported user types (Individual,
 * Organization, Family, Government, Educational Institution, etc.).
 * 
 * Key Features:
 * - Multi-user type support with dynamic organizational hierarchy
 * - Position-based access control and permissions
 * - Activity tracking and audit logging
 * - API token management for mobile applications
 * - Security and compliance features
 * 
 * @package App\Models
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',              // Full name of the user
        'first_name',        // First name (computed from name)
        'last_name',         // Last name (computed from name)
        'type',              // User type (individual, organisation, family, etc.)
        'type_name',         // Specific organization/entity name
        'email',             // User's email address (unique)
        'password',          // Hashed password
        'phone',             // Phone number for SMS notifications
        'avatar',            // Profile picture path
        'is_active',         // Account status flag
        'is_admin',          // Administrative privileges flag
        'last_login_at',     // Last successful login timestamp
        'email_verified_at', // Email verification timestamp
        'last_seen_at',      // Last activity timestamp
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * @var array<int, string>
     */
    protected $hidden = [
        'password',        // Never expose password in API responses
        'remember_token',  // Never expose remember token
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Update the user's last login timestamp.
     * 
     * This method is called after successful authentication to track user login activity
     * for security monitoring and analytics purposes.
     * 
     * @return void
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Update the user's last seen timestamp.
     * 
     * This method is called during user activity to track presence and activity
     * for real-time collaboration features and user engagement analytics.
     * 
     * @return void
     */
    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * Get all positions assigned to this user.
     * 
     * Users can have multiple positions within different departments/roles,
     * supporting complex organizational structures and role-based access control.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'user_positions')
                    ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get the user's primary position.
     * 
     * Returns the main position that defines the user's primary role and permissions
     * within the organizational hierarchy.
     * 
     * @return \App\Models\Position|null
     */
    public function primaryPosition()
    {
        return $this->positions()->wherePivot('is_primary', true)->wherePivot('is_active', true)->first();
    }

    /**
     * Get all active positions for this user.
     * 
     * Returns positions that are currently active, excluding historical or future assignments.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activePositions()
    {
        return $this->positions()->wherePivot('is_active', true);
    }

    /**
     * Get all departments this user belongs to through their positions.
     * 
     * Uses a many-through relationship to access departments via the user's positions,
     * supporting the organizational hierarchy structure.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function departments()
    {
        return $this->hasManyThrough(Department::class, Position::class, 'department_id', 'id', 'id', 'department_id');
    }

    /**
     * Get the first name from the full name.
     * 
     * Extracts the first word from the user's full name for display purposes.
     * 
     * @return string
     */
    public function getFirstNameAttribute()
    {
        $nameParts = explode(' ', $this->name);
        return $nameParts[0] ?? '';
    }

    /**
     * Get the last name from the full name.
     * 
     * Extracts all words except the first from the user's full name.
     * Handles multiple last names (e.g., "John Smith Johnson" -> "Smith Johnson").
     * 
     * @return string
     */
    public function getLastNameAttribute()
    {
        $nameParts = explode(' ', $this->name);
        if (count($nameParts) > 1) {
            array_shift($nameParts); // Remove first name
            return implode(' ', $nameParts); // Join remaining parts as last name
        }
        return '';
    }

    /**
     * Get the full name of the user.
     * 
     * Returns the complete name as stored in the database.
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get file permissions assigned to this user.
     * 
     * Returns all file permissions directly assigned to this user,
     * excluding permissions inherited through positions or departments.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filePermissions()
    {
        return $this->hasMany(FilePermission::class);
    }

    /**
     * Get files uploaded by this user.
     * 
     * Returns all files that were uploaded by this user, regardless of
     * current permissions or organizational changes.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    /**
     * Get activities performed by this user.
     * 
     * Returns all user activities for audit trails, analytics, and
     * activity feed generation.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(RecentActivity::class);
    }

    /**
     * Get comments made by this user.
     * 
     * Returns all comments authored by this user across files and documents
     * for collaboration tracking and user engagement analytics.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get document locks created by this user.
     * 
     * Returns all document locks initiated by this user for collaborative
     * editing and conflict prevention.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentLocks()
    {
        return $this->hasMany(DocumentLock::class);
    }

    /**
     * Get workflow instances initiated by this user.
     * 
     * Returns all workflow instances started by this user for approval
     * processes and business process tracking.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'initiated_by');
    }

    /**
     * Get security audits for this user.
     * 
     * Returns all security-related audit logs for this user for compliance
     * monitoring and security analysis.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function securityAudits()
    {
        return $this->hasMany(SecurityAudit::class);
    }
}
