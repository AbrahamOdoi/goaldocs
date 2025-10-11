<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CollaborationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'session_id',
        'session_name',
        'created_by',
        'is_active',
        'last_activity',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
        'settings' => 'array'
    ];

    /**
     * Boot method to generate session ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($session) {
            if (empty($session->session_id)) {
                $session->session_id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the file that owns the session
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created the session
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the active presences for this session
     */
    public function presences(): HasMany
    {
        return $this->hasMany(UserPresence::class, 'session_id');
    }

    /**
     * Get active users in this session
     */
    public function activeUsers()
    {
        return $this->presences()
            ->with('user')
            ->where('status', 'online')
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->get()
            ->pluck('user');
    }

    /**
     * Get all users in this session
     */
    public function allUsers()
    {
        return $this->presences()
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id');
    }

    /**
     * Check if user is in this session
     */
    public function hasUser($userId): bool
    {
        return $this->presences()
            ->where('user_id', $userId)
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->exists();
    }

    /**
     * Add user to session
     */
    public function addUser($userId, $connectionId = null): UserPresence
    {
        $connectionId = $connectionId ?? Str::uuid()->toString();
        
        return UserPresence::updateOrCreate(
            [
                'user_id' => $userId,
                'session_id' => $this->id
            ],
            [
                'connection_id' => $connectionId,
                'status' => 'online',
                'last_seen' => now()
            ]
        );
    }

    /**
     * Remove user from session
     */
    public function removeUser($userId): bool
    {
        return $this->presences()
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Update user presence
     */
    public function updateUserPresence($userId, $data = []): bool
    {
        return $this->presences()
            ->where('user_id', $userId)
            ->update(array_merge($data, ['last_seen' => now()]));
    }

    /**
     * Get session statistics
     */
    public function getStats(): array
    {
        $totalUsers = $this->presences()->count();
        $activeUsers = $this->presences()
            ->where('status', 'online')
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->count();
        
        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'session_duration' => $this->created_at->diffInMinutes(now()),
            'last_activity' => $this->last_activity
        ];
    }

    /**
     * Clean up inactive sessions
     */
    public static function cleanupInactiveSessions()
    {
        return static::where('last_activity', '<', now()->subHours(24))
            ->update(['is_active' => false]);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for sessions by file
     */
    public function scopeForFile($query, $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    /**
     * Get session data for frontend
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        $data['creator_name'] = $this->creator->name ?? 'Unknown';
        $data['file_name'] = $this->file->original_name ?? 'Unknown';
        $data['active_users_count'] = $this->activeUsers()->count();
        $data['total_users_count'] = $this->allUsers()->count();
        $data['active_users'] = $this->activeUsers()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null
            ];
        });
        
        return $data;
    }
}
