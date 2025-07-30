<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_id',
        'assignable_type',
        'assignable_id',
        'role',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'due_date',
        'action_taken',
        'action_notes',
        'action_metadata',
        'delegated_to',
        'delegation_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
        'action_metadata' => 'array',
    ];

    protected $dates = [
        'assigned_at',
        'started_at',
        'completed_at',
        'due_date',
    ];

    /**
     * Get the workflow this assignment belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflow::class, 'workflow_id');
    }

    /**
     * Get the step this assignment belongs to
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    /**
     * Get the assignable entity (User, Position, Department)
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user this assignment was delegated to
     */
    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed assignments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for assigned assignments
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for delegated assignments
     */
    public function scopeDelegated($query)
    {
        return $query->where('status', 'delegated');
    }

    /**
     * Scope for assignments assigned to a specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('assignable_type', User::class)
              ->where('assignable_id', $user->id)
              ->orWhere('delegated_to', $user->id);
        });
    }

    /**
     * Scope for overdue assignments
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'delegated']);
    }

    /**
     * Check if assignment is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if assignment is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if assignment is assigned
     */
    public function getIsAssignedAttribute(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if assignment is delegated
     */
    public function getIsDelegatedAttribute(): bool
    {
        return $this->status === 'delegated';
    }

    /**
     * Check if assignment is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted;
    }

    /**
     * Get the role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return ucfirst($this->role);
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the action taken display name
     */
    public function getActionTakenDisplayAttribute(): string
    {
        if (!$this->action_taken) {
            return 'No Action';
        }
        return ucfirst(str_replace('_', ' ', $this->action_taken));
    }

    /**
     * Start this assignment
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete this assignment
     */
    public function complete(string $action = 'approved', ?string $notes = null, ?array $metadata = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'action_taken' => $action,
            'action_notes' => $notes,
            'action_metadata' => $metadata,
        ]);
    }

    /**
     * Delegate this assignment
     */
    public function delegate(User $delegateTo, ?string $reason = null): void
    {
        $this->update([
            'status' => 'delegated',
            'delegated_to' => $delegateTo->id,
            'delegation_reason' => $reason,
        ]);
    }

    /**
     * Get the assignee display name
     */
    public function getAssigneeDisplayAttribute(): string
    {
        if ($this->delegatedTo) {
            return $this->delegatedTo->full_name . ' (Delegated)';
        }

        if ($this->assignable) {
            if ($this->assignable_type === User::class) {
                return $this->assignable->full_name;
            } else {
                return $this->assignable->name ?? 'Unknown';
            }
        }

        return 'Unassigned';
    }

    /**
     * Get the assignable type display name
     */
    public function getAssignableTypeDisplayAttribute(): string
    {
        $type = class_basename($this->assignable_type);
        return ucfirst($type);
    }

    /**
     * Check if assignment can be started
     */
    public function getCanStartAttribute(): bool
    {
        return $this->isAssigned;
    }

    /**
     * Check if assignment can be completed
     */
    public function getCanCompleteAttribute(): bool
    {
        return $this->isActive;
    }

    /**
     * Check if assignment can be delegated
     */
    public function getCanDelegateAttribute(): bool
    {
        return $this->isAssigned || $this->isActive;
    }
}
