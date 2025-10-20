<?php

/**
 * RecentActivity Model - Activity Tracking and Audit Logging for GoalDocs Enterprise System
 * 
 * This model represents the activity tracking system in GoalDocs, providing comprehensive
 * audit logging and user activity monitoring for compliance, analytics, and user engagement.
 * 
 * Key Features:
 * - Comprehensive activity tracking across all user actions
 * - Multi-user type support with organizational context
 * - File and folder activity monitoring
 * - Workflow and collaboration activity tracking
 * - Metadata storage for detailed activity information
 * - Activity feed generation for users and organizations
 * - Compliance and audit trail capabilities
 * 
 * Activity Types:
 * - File operations: view, download, upload, edit, delete
 * - Sharing and permissions: share, permission_change
 * - Collaboration: comment, resolve_comment, reopen_comment, delete_comment
 * - Organization: favorite, tag
 * - Workflow: workflow, workflow_step, workflow_assignment
 * 
 * Use Cases:
 * - User activity dashboards and feeds
 * - Compliance reporting and audit trails
 * - Analytics and usage statistics
 * - Security monitoring and anomaly detection
 * - User engagement tracking
 * 
 * @package App\Models
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',        // User who performed the activity
        'file_id',        // File involved in the activity (optional)
        'folder_id',      // Folder involved in the activity (optional)
        'activity_type',  // Type of activity performed
        'metadata',       // Additional activity metadata (JSON)
        'user_type',      // User type context
        'type_name',      // Organization/entity name
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',     // Cast metadata to array
        'created_at' => 'datetime', // Cast timestamps to datetime
        'updated_at' => 'datetime',
    ];

    /**
     * Activity type constants with human-readable labels.
     * 
     * Defines all supported activity types and their display labels
     * for consistent activity tracking across the system.
     * 
     * @var array<string, string>
     */
    const ACTIVITY_TYPES = [
        'view' => 'Viewed',
        'download' => 'Downloaded',
        'upload' => 'Uploaded',
        'edit' => 'Edited',
        'delete' => 'Deleted',
        'share' => 'Shared',
        'permission_change' => 'Changed Permissions',
        'favorite' => 'Favorited',
        'tag' => 'Tagged',
        'comment' => 'Commented',
        'workflow' => 'Workflow',
        'workflow_step' => 'Workflow Step',
        'workflow_assignment' => 'Workflow Assignment',
        'resolve_comment' => 'Resolved Comment',
        'reopen_comment' => 'Reopened Comment',
        'delete_comment' => 'Deleted Comment',
    ];

    /**
     * Get the user who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file involved in this activity
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the folder involved in this activity
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the resource involved in this activity (file or folder)
     */
    public function resource()
    {
        return $this->file ?? $this->folder;
    }

    /**
     * Get the activity type label
     */
    public function getActivityTypeLabelAttribute(): string
    {
        return self::ACTIVITY_TYPES[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Scope for filtering by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for filtering by specific organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)->where('type_name', $typeName);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by activity type
     */
    public function scopeByActivityType($query, $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Scope for recent activities (last 30 days)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Log a new activity entry.
     * 
     * Creates a new activity record for tracking user actions across the system.
     * This method is used throughout the application to maintain comprehensive
     * audit trails and activity feeds.
     * 
     * @param int $userId The user who performed the activity
     * @param string $userType The user type context
     * @param string|null $typeName The organization/entity name
     * @param string $activityType The type of activity performed
     * @param int|null $fileId The file involved (optional)
     * @param int|null $folderId The folder involved (optional)
     * @param array|null $metadata Additional activity metadata (optional)
     * @return self The created activity record
     */
    public static function log($userId, $userType, $typeName, $activityType, $fileId = null, $folderId = null, $metadata = null): self
    {
        $data = [
            'user_id' => $userId,
            'file_id' => $fileId,
            'folder_id' => $folderId,
            'activity_type' => $activityType,
            'metadata' => $metadata,
            'user_type' => $userType,
        ];
        
        // Only set type_name if it's not null or empty
        if (!empty($typeName)) {
            $data['type_name'] = $typeName;
        }
        
        return static::create($data);
    }

    /**
     * Get recent activities for a specific user.
     * 
     * Retrieves the most recent activities performed by a user within their
     * organizational context, useful for personal activity feeds and dashboards.
     * 
     * @param int $userId The user ID to get activities for
     * @param string $userType The user type context
     * @param string|null $typeName The organization/entity name
     * @param int $limit Maximum number of activities to return
     * @return \Illuminate\Database\Eloquent\Collection Recent user activities
     */
    public static function getRecentForUser($userId, $userType, $typeName, $limit = 20)
    {
        return static::forUser($userId)
            ->forOrganization($userType, $typeName)
            ->with(['file', 'folder', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activities for an organization.
     * 
     * Retrieves the most recent activities across all users within an organization,
     * useful for organizational dashboards and compliance reporting.
     * 
     * @param string $userType The user type context
     * @param string|null $typeName The organization/entity name
     * @param int $limit Maximum number of activities to return
     * @return \Illuminate\Database\Eloquent\Collection Recent organizational activities
     */
    public static function getRecentForOrganization($userType, $typeName, $limit = 50)
    {
        return static::forOrganization($userType, $typeName)
            ->with(['file', 'folder', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
