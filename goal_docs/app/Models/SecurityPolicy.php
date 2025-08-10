<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'settings',
        'is_active',
        'is_global',
        'applies_to',
        'effective_from',
        'effective_until',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'applies_to' => 'array',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    // Policy types
    const TYPE_PASSWORD = 'password';
    const TYPE_SESSION = 'session';
    const TYPE_MFA = 'mfa';
    const TYPE_ENCRYPTION = 'encryption';
    const TYPE_ACCESS_CONTROL = 'access_control';
    const TYPE_AUDIT = 'audit';
    const TYPE_DATA_RETENTION = 'data_retention';
    const TYPE_PRIVACY = 'privacy';

    /**
     * Get the user who created the policy.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the policy.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for global policies.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for policies by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for currently effective policies.
     */
    public function scopeCurrentlyEffective($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', now());
        });
    }

    /**
     * Scope for policies that apply to a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_global', true)
              ->orWhereJsonContains('applies_to', ['users' => [$userId]]);
        });
    }

    /**
     * Check if policy is currently effective.
     */
    public function isCurrentlyEffective(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_from && $this->effective_from->isFuture()) {
            return false;
        }

        if ($this->effective_until && $this->effective_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if policy applies to a specific user.
     */
    public function appliesToUser($userId): bool
    {
        if ($this->is_global) {
            return true;
        }

        if (!$this->applies_to) {
            return false;
        }

        $users = $this->applies_to['users'] ?? [];
        return in_array($userId, $users);
    }

    /**
     * Get policy type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PASSWORD => 'Password Policy',
            self::TYPE_SESSION => 'Session Policy',
            self::TYPE_MFA => 'Multi-Factor Authentication',
            self::TYPE_ENCRYPTION => 'Encryption Policy',
            self::TYPE_ACCESS_CONTROL => 'Access Control',
            self::TYPE_AUDIT => 'Audit Policy',
            self::TYPE_DATA_RETENTION => 'Data Retention',
            self::TYPE_PRIVACY => 'Privacy Policy',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get policy type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PASSWORD => 'ti ti-lock',
            self::TYPE_SESSION => 'ti ti-clock',
            self::TYPE_MFA => 'ti ti-shield-check',
            self::TYPE_ENCRYPTION => 'ti ti-key',
            self::TYPE_ACCESS_CONTROL => 'ti ti-user-check',
            self::TYPE_AUDIT => 'ti ti-file-text',
            self::TYPE_DATA_RETENTION => 'ti ti-database',
            self::TYPE_PRIVACY => 'ti ti-eye-off',
            default => 'ti ti-settings',
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge bg-label-secondary';
        }

        if (!$this->isCurrentlyEffective()) {
            return 'badge bg-label-warning';
        }

        return 'badge bg-label-success';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if (!$this->isCurrentlyEffective()) {
            return 'Not Effective';
        }

        return 'Active';
    }

    /**
     * Get scope label.
     */
    public function getScopeLabelAttribute(): string
    {
        if ($this->is_global) {
            return 'Global';
        }

        if ($this->applies_to) {
            $users = count($this->applies_to['users'] ?? []);
            $roles = count($this->applies_to['roles'] ?? []);
            $groups = count($this->applies_to['groups'] ?? []);
            
            $scopes = [];
            if ($users > 0) $scopes[] = $users . ' users';
            if ($roles > 0) $scopes[] = $roles . ' roles';
            if ($groups > 0) $scopes[] = $groups . ' groups';
            
            return implode(', ', $scopes);
        }

        return 'None';
    }

    /**
     * Get effective period.
     */
    public function getEffectivePeriodAttribute(): string
    {
        if (!$this->effective_from && !$this->effective_until) {
            return 'Always';
        }

        $from = $this->effective_from ? $this->effective_from->format('M j, Y') : 'Always';
        $until = $this->effective_until ? $this->effective_until->format('M j, Y') : 'Always';

        return $from . ' to ' . $until;
    }

    /**
     * Get setting value.
     */
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set setting value.
     */
    public function setSetting($key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
    }

    /**
     * Get password policy settings.
     */
    public function getPasswordPolicySettings(): array
    {
        if ($this->type !== self::TYPE_PASSWORD) {
            return [];
        }

        return [
            'min_length' => $this->getSetting('min_length', 8),
            'require_uppercase' => $this->getSetting('require_uppercase', true),
            'require_lowercase' => $this->getSetting('require_lowercase', true),
            'require_numbers' => $this->getSetting('require_numbers', true),
            'require_symbols' => $this->getSetting('require_symbols', true),
            'max_age_days' => $this->getSetting('max_age_days', 90),
            'prevent_reuse' => $this->getSetting('prevent_reuse', 5),
        ];
    }

    /**
     * Get session policy settings.
     */
    public function getSessionPolicySettings(): array
    {
        if ($this->type !== self::TYPE_SESSION) {
            return [];
        }

        return [
            'timeout_minutes' => $this->getSetting('timeout_minutes', 120),
            'max_concurrent_sessions' => $this->getSetting('max_concurrent_sessions', 5),
            'require_secure_connection' => $this->getSetting('require_secure_connection', true),
            'auto_logout_inactive' => $this->getSetting('auto_logout_inactive', true),
            'inactive_timeout_minutes' => $this->getSetting('inactive_timeout_minutes', 30),
        ];
    }

    /**
     * Get MFA policy settings.
     */
    public function getMfaPolicySettings(): array
    {
        if ($this->type !== self::TYPE_MFA) {
            return [];
        }

        return [
            'enabled' => $this->getSetting('enabled', false),
            'required_for_all' => $this->getSetting('required_for_all', false),
            'required_for_admin' => $this->getSetting('required_for_admin', true),
            'methods' => $this->getSetting('methods', ['totp']),
            'backup_codes_enabled' => $this->getSetting('backup_codes_enabled', true),
            'remember_device_days' => $this->getSetting('remember_device_days', 30),
        ];
    }
}
