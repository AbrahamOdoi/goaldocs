<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'severity',
        'description',
        'ip_address',
        'user_agent',
        'location',
        'metadata',
        'is_suspicious',
        'requires_review',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_suspicious' => 'boolean',
        'requires_review' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // Event types
    const EVENT_LOGIN = 'login';
    const EVENT_LOGOUT = 'logout';
    const EVENT_LOGIN_FAILED = 'login_failed';
    const EVENT_PASSWORD_CHANGE = 'password_change';
    const EVENT_MFA_ENABLED = 'mfa_enabled';
    const EVENT_MFA_DISABLED = 'mfa_disabled';
    const EVENT_FILE_ACCESS = 'file_access';
    const EVENT_FILE_DOWNLOAD = 'file_download';
    const EVENT_PERMISSION_CHANGE = 'permission_change';
    const EVENT_SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    const EVENT_SESSION_EXPIRED = 'session_expired';
    const EVENT_ACCOUNT_LOCKED = 'account_locked';
    const EVENT_ACCOUNT_UNLOCKED = 'account_unlocked';

    // Severity levels
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Get the user that owns the security log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for suspicious events.
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope for events requiring review.
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true);
    }

    /**
     * Scope for unresolved events.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope for events by severity.
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for events by type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for recent events.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark event as resolved.
     */
    public function markAsResolved($notes = null): void
    {
        $this->update([
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark event as suspicious.
     */
    public function markAsSuspicious(): void
    {
        $this->update([
            'is_suspicious' => true,
            'requires_review' => true,
        ]);
    }

    /**
     * Get event type label.
     */
    public function getEventTypeLabelAttribute(): string
    {
        return match($this->event_type) {
            self::EVENT_LOGIN => 'Login',
            self::EVENT_LOGOUT => 'Logout',
            self::EVENT_LOGIN_FAILED => 'Login Failed',
            self::EVENT_PASSWORD_CHANGE => 'Password Change',
            self::EVENT_MFA_ENABLED => 'MFA Enabled',
            self::EVENT_MFA_DISABLED => 'MFA Disabled',
            self::EVENT_FILE_ACCESS => 'File Access',
            self::EVENT_FILE_DOWNLOAD => 'File Download',
            self::EVENT_PERMISSION_CHANGE => 'Permission Change',
            self::EVENT_SUSPICIOUS_ACTIVITY => 'Suspicious Activity',
            self::EVENT_SESSION_EXPIRED => 'Session Expired',
            self::EVENT_ACCOUNT_LOCKED => 'Account Locked',
            self::EVENT_ACCOUNT_UNLOCKED => 'Account Unlocked',
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    /**
     * Get severity badge class.
     */
    public function getSeverityBadgeClassAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_LOW => 'badge bg-label-info',
            self::SEVERITY_MEDIUM => 'badge bg-label-warning',
            self::SEVERITY_HIGH => 'badge bg-label-danger',
            self::SEVERITY_CRITICAL => 'badge bg-danger',
            default => 'badge bg-label-secondary',
        };
    }

    /**
     * Check if event is resolved.
     */
    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    /**
     * Get location information.
     */
    public function getLocationInfoAttribute(): string
    {
        if ($this->location) {
            return $this->location;
        }

        if ($this->ip_address) {
            return $this->ip_address;
        }

        return 'Unknown';
    }
}
