<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPresence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'connection_id',
        'status',
        'current_page',
        'cursor_position',
        'activity_data',
        'last_seen'
    ];

    protected $casts = [
        'cursor_position' => 'array',
        'activity_data' => 'array',
        'last_seen' => 'datetime'
    ];

    // Status constants
    const STATUS_ONLINE = 'online';
    const STATUS_AWAY = 'away';
    const STATUS_BUSY = 'busy';
    const STATUS_OFFLINE = 'offline';

    /**
     * Get the user that owns the presence
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the session that owns the presence
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CollaborationSession::class, 'session_id');
    }

    /**
     * Check if user is currently active
     */
    public function isActive(): bool
    {
        return $this->last_seen && $this->last_seen->isAfter(now()->subMinutes(5));
    }

    /**
     * Update user's current page
     */
    public function updatePage(int $pageNumber): bool
    {
        return $this->update([
            'current_page' => $pageNumber,
            'last_seen' => now()
        ]);
    }

    /**
     * Update user's cursor position
     */
    public function updateCursorPosition(array $position): bool
    {
        return $this->update([
            'cursor_position' => $position,
            'last_seen' => now()
        ]);
    }

    /**
     * Update user's activity data
     */
    public function updateActivityData(array $data): bool
    {
        return $this->update([
            'activity_data' => array_merge($this->activity_data ?? [], $data),
            'last_seen' => now()
        ]);
    }

    /**
     * Set user status
     */
    public function setStatus(string $status): bool
    {
        if (!in_array($status, [self::STATUS_ONLINE, self::STATUS_AWAY, self::STATUS_BUSY, self::STATUS_OFFLINE])) {
            return false;
        }

        return $this->update([
            'status' => $status,
            'last_seen' => now()
        ]);
    }

    /**
     * Mark user as online
     */
    public function markOnline(): bool
    {
        return $this->setStatus(self::STATUS_ONLINE);
    }

    /**
     * Mark user as away
     */
    public function markAway(): bool
    {
        return $this->setStatus(self::STATUS_AWAY);
    }

    /**
     * Mark user as busy
     */
    public function markBusy(): bool
    {
        return $this->setStatus(self::STATUS_BUSY);
    }

    /**
     * Mark user as offline
     */
    public function markOffline(): bool
    {
        return $this->setStatus(self::STATUS_OFFLINE);
    }

    /**
     * Get user's current activity summary
     */
    public function getActivitySummary(): array
    {
        return [
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? 'Unknown',
            'status' => $this->status,
            'current_page' => $this->current_page,
            'cursor_position' => $this->cursor_position,
            'last_seen' => $this->last_seen,
            'is_active' => $this->isActive(),
            'activity_data' => $this->activity_data
        ];
    }

    /**
     * Scope for active presences
     */
    public function scopeActive($query)
    {
        return $query->where('last_seen', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for online users
     */
    public function scopeOnline($query)
    {
        return $query->where('status', self::STATUS_ONLINE);
    }

    /**
     * Scope for presences by session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Clean up old presence records
     */
    public static function cleanupOldPresences()
    {
        return static::where('last_seen', '<', now()->subHours(24))->delete();
    }

    /**
     * Get presence data for frontend
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        $data['user_name'] = $this->user->name ?? 'Unknown';
        $data['user_avatar'] = $this->user->avatar ?? null;
        $data['is_active'] = $this->isActive();
        $data['activity_summary'] = $this->getActivitySummary();
        
        return $data;
    }
}
