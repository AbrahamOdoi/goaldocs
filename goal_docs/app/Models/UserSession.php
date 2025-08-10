<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'location',
        'device_type',
        'browser',
        'os',
        'is_active',
        'is_secure',
        'is_mfa_verified',
        'last_activity',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_secure' => 'boolean',
        'is_mfa_verified' => 'boolean',
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Device types
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_MOBILE = 'mobile';
    const DEVICE_TABLET = 'tablet';

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for expired sessions.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for secure sessions.
     */
    public function scopeSecure($query)
    {
        return $query->where('is_secure', true);
    }

    /**
     * Scope for MFA verified sessions.
     */
    public function scopeMfaVerified($query)
    {
        return $query->where('is_mfa_verified', true);
    }

    /**
     * Scope for sessions by device type.
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope for recent sessions.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for sessions by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if session is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if session is secure.
     */
    public function isSecure(): bool
    {
        return $this->is_secure;
    }

    /**
     * Check if session is MFA verified.
     */
    public function isMfaVerified(): bool
    {
        return $this->is_mfa_verified;
    }

    /**
     * Update last activity.
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Deactivate session.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Extend session.
     */
    public function extend($minutes = 120): void
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes),
            'last_activity' => now(),
        ]);
    }

    /**
     * Get device type label.
     */
    public function getDeviceTypeLabelAttribute(): string
    {
        return match($this->device_type) {
            self::DEVICE_DESKTOP => 'Desktop',
            self::DEVICE_MOBILE => 'Mobile',
            self::DEVICE_TABLET => 'Tablet',
            default => 'Unknown',
        };
    }

    /**
     * Get device type icon.
     */
    public function getDeviceTypeIconAttribute(): string
    {
        return match($this->device_type) {
            self::DEVICE_DESKTOP => 'ti ti-device-desktop',
            self::DEVICE_MOBILE => 'ti ti-device-mobile',
            self::DEVICE_TABLET => 'ti ti-device-tablet',
            default => 'ti ti-device',
        };
    }

    /**
     * Get session status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge bg-label-secondary';
        }

        if ($this->isExpired()) {
            return 'badge bg-label-warning';
        }

        if ($this->is_secure && $this->is_mfa_verified) {
            return 'badge bg-label-success';
        }

        if ($this->is_secure) {
            return 'badge bg-label-info';
        }

        return 'badge bg-label-warning';
    }

    /**
     * Get session status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->is_secure && $this->is_mfa_verified) {
            return 'Secure + MFA';
        }

        if ($this->is_secure) {
            return 'Secure';
        }

        return 'Active';
    }

    /**
     * Get location information.
     */
    public function getLocationInfoAttribute(): string
    {
        if ($this->location) {
            return $this->location;
        }

        return $this->ip_address ?? 'Unknown';
    }

    /**
     * Get session duration.
     */
    public function getSessionDurationAttribute(): string
    {
        if (!$this->last_activity) {
            return '0 minutes';
        }

        $duration = $this->created_at->diffInMinutes($this->last_activity);
        
        if ($duration < 60) {
            return $duration . ' minutes';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        
        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Get time until expiration.
     */
    public function getTimeUntilExpirationAttribute(): string
    {
        if (!$this->expires_at) {
            return 'No expiration';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        $minutes = now()->diffInMinutes($this->expires_at, false);
        
        if ($minutes < 60) {
            return $minutes . ' minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . 'h ' . $remainingMinutes . 'm';
    }
}
