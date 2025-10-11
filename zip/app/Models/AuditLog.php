<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'resource_name',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'is_compliance_related',
        'compliance_standard',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'is_compliance_related' => 'boolean',
    ];

    // Actions
    const ACTION_CREATE = 'create';
    const ACTION_READ = 'read';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_PERMISSION_CHANGE = 'permission_change';

    // Resource types
    const RESOURCE_USER = 'user';
    const RESOURCE_FILE = 'file';
    const RESOURCE_DOCUMENT = 'document';
    const RESOURCE_FOLDER = 'folder';
    const RESOURCE_REPORT = 'report';
    const RESOURCE_SETTING = 'setting';
    const RESOURCE_POLICY = 'policy';

    // Compliance standards
    const COMPLIANCE_GDPR = 'GDPR';
    const COMPLIANCE_HIPAA = 'HIPAA';
    const COMPLIANCE_SOX = 'SOX';
    const COMPLIANCE_PCI = 'PCI';
    const COMPLIANCE_ISO27001 = 'ISO27001';

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for compliance-related logs.
     */
    public function scopeComplianceRelated($query)
    {
        return $query->where('is_compliance_related', true);
    }

    /**
     * Scope for logs by compliance standard.
     */
    public function scopeByComplianceStandard($query, $standard)
    {
        return $query->where('compliance_standard', $standard);
    }

    /**
     * Scope for logs by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for logs by resource type.
     */
    public function scopeByResourceType($query, $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope for logs by resource.
     */
    public function scopeByResource($query, $resourceType, $resourceId)
    {
        return $query->where('resource_type', $resourceType)
                    ->where('resource_id', $resourceId);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for logs by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => 'Created',
            self::ACTION_READ => 'Viewed',
            self::ACTION_UPDATE => 'Updated',
            self::ACTION_DELETE => 'Deleted',
            self::ACTION_EXPORT => 'Exported',
            self::ACTION_IMPORT => 'Imported',
            self::ACTION_LOGIN => 'Logged In',
            self::ACTION_LOGOUT => 'Logged Out',
            self::ACTION_PERMISSION_CHANGE => 'Permission Changed',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get resource type label.
     */
    public function getResourceTypeLabelAttribute(): string
    {
        return match($this->resource_type) {
            self::RESOURCE_USER => 'User',
            self::RESOURCE_FILE => 'File',
            self::RESOURCE_DOCUMENT => 'Document',
            self::RESOURCE_FOLDER => 'Folder',
            self::RESOURCE_REPORT => 'Report',
            self::RESOURCE_SETTING => 'Setting',
            self::RESOURCE_POLICY => 'Policy',
            default => ucfirst($this->resource_type),
        };
    }

    /**
     * Get compliance standard badge class.
     */
    public function getComplianceBadgeClassAttribute(): string
    {
        return match($this->compliance_standard) {
            self::COMPLIANCE_GDPR => 'badge bg-label-primary',
            self::COMPLIANCE_HIPAA => 'badge bg-label-success',
            self::COMPLIANCE_SOX => 'badge bg-label-warning',
            self::COMPLIANCE_PCI => 'badge bg-label-danger',
            self::COMPLIANCE_ISO27001 => 'badge bg-label-info',
            default => 'badge bg-label-secondary',
        };
    }

    /**
     * Get changes summary.
     */
    public function getChangesSummaryAttribute(): string
    {
        if ($this->action === self::ACTION_CREATE) {
            return 'Resource created';
        }

        if ($this->action === self::ACTION_DELETE) {
            return 'Resource deleted';
        }

        if ($this->action === self::ACTION_UPDATE && $this->old_values && $this->new_values) {
            $changes = [];
            foreach ($this->new_values as $field => $newValue) {
                $oldValue = $this->old_values[$field] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[] = ucfirst($field);
                }
            }
            return count($changes) > 0 ? 'Updated: ' . implode(', ', $changes) : 'No changes detected';
        }

        return $this->description;
    }

    /**
     * Check if log is compliance-related.
     */
    public function isComplianceRelated(): bool
    {
        return $this->is_compliance_related;
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }
}
