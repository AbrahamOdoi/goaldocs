<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_id',
        'folder_id',
        'activity_type',
        'metadata',
        'user_type',
        'type_name',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Activity types
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
     * Log an activity
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
     * Get recent activities for a user
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
     * Get recent activities for an organization
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
