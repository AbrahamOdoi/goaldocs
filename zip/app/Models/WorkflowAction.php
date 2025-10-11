<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_instance_id',
        'step_id',
        'action_type',
        'assigned_to',
        'acted_by',
        'acted_at',
        'due_date',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow instance this action belongs to
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Get the workflow step this action is for
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * Get the user assigned to this action
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who acted on this action
     */
    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    /**
     * Scope to get only pending actions
     */
    public function scopePending($query)
    {
        return $query->where('action_type', 'pending');
    }

    /**
     * Scope to get only approved actions
     */
    public function scopeApproved($query)
    {
        return $query->where('action_type', 'approved');
    }

    /**
     * Scope to get only rejected actions
     */
    public function scopeRejected($query)
    {
        return $query->where('action_type', 'rejected');
    }

    /**
     * Scope to get only overdue actions
     */
    public function scopeOverdue($query)
    {
        return $query->where('action_type', 'pending')
            ->where('due_date', '<', now());
    }

    /**
     * Check if action is pending
     */
    public function isPending(): bool
    {
        return $this->action_type === 'pending';
    }

    /**
     * Check if action is approved
     */
    public function isApproved(): bool
    {
        return $this->action_type === 'approved';
    }

    /**
     * Check if action is rejected
     */
    public function isRejected(): bool
    {
        return $this->action_type === 'rejected';
    }

    /**
     * Check if action is overdue
     */
    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date->isPast();
    }

    /**
     * Check if action can be acted upon by user
     */
    public function canBeActedBy(int $userId): bool
    {
        return $this->isPending() && $this->assigned_to === $userId;
    }

    /**
     * Get action status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        switch ($this->action_type) {
            case 'pending':
                return $this->isOverdue() ? 'Overdue' : 'Pending';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            case 'expired':
                return 'Expired';
            default:
                return ucfirst($this->action_type);
        }
    }

    /**
     * Get action type display name
     */
    public function getActionTypeDisplayAttribute(): string
    {
        return ucfirst($this->action_type);
    }

    /**
     * Get remaining time in hours
     */
    public function getRemainingHours(): int
    {
        if (!$this->isPending()) {
            return 0;
        }

        return max(0, now()->diffInHours($this->due_date, false));
    }

    /**
     * Get remaining time display
     */
    public function getRemainingTimeDisplayAttribute(): string
    {
        if (!$this->isPending()) {
            return 'N/A';
        }

        $hours = $this->getRemainingHours();
        
        if ($hours < 0) {
            return 'Overdue';
        }

        if ($hours < 1) {
            return 'Less than 1 hour';
        }

        if ($hours < 24) {
            return "{$hours} hours";
        }

        $days = floor($hours / 24);
        return "{$days} days";
    }
}
