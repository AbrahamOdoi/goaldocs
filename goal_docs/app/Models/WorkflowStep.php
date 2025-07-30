<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'order',
        'type',
        'config',
        'status',
        'assignee_type',
        'assignee_id',
        'assignee_name',
        'timeout_hours',
        'started_at',
        'completed_at',
        'due_date',
        'action_taken',
        'action_notes',
        'action_by',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'completed_at',
        'due_date',
    ];

    /**
     * Get the workflow this step belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflow::class, 'workflow_id');
    }

    /**
     * Get the assignee of this step
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the user who took action on this step
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Get the assignments for this step
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(WorkflowAssignment::class, 'step_id');
    }

    /**
     * Scope for active steps
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed steps
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending steps
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for steps assigned to a user
     */
    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assignee_id', $user->id);
    }

    /**
     * Scope for overdue steps
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'skipped']);
    }

    /**
     * Check if step is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if step is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if step is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if step is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted;
    }

    /**
     * Get the step type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return ucfirst($this->type);
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst($this->status);
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
     * Start this step
     */
    public function start(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete this step
     */
    public function complete(string $action = 'approved', ?string $notes = null, ?User $actionBy = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'action_taken' => $action,
            'action_notes' => $notes,
            'action_by' => $actionBy ? $actionBy->id : auth()->id(),
        ]);
    }

    /**
     * Skip this step
     */
    public function skip(?string $reason = null): void
    {
        $this->update([
            'status' => 'skipped',
            'action_taken' => 'skipped',
            'action_notes' => $reason,
            'action_by' => auth()->id(),
        ]);
    }

    /**
     * Fail this step
     */
    public function fail(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'action_taken' => 'rejected',
            'action_notes' => $reason,
            'action_by' => auth()->id(),
        ]);
    }

    /**
     * Get the assignee display name
     */
    public function getAssigneeDisplayAttribute(): string
    {
        if ($this->assignee_name) {
            return $this->assignee_name;
        }

        if ($this->assignee) {
            return $this->assignee->full_name;
        }

        return 'Unassigned';
    }

    /**
     * Get the assignee type display name
     */
    public function getAssigneeTypeDisplayAttribute(): string
    {
        return ucfirst($this->assignee_type);
    }

    /**
     * Check if step can be completed
     */
    public function getCanCompleteAttribute(): bool
    {
        return $this->isActive && !$this->isCompleted;
    }

    /**
     * Check if step can be skipped
     */
    public function getCanSkipAttribute(): bool
    {
        return $this->isActive && !$this->isCompleted;
    }

    /**
     * Get the next step in the workflow
     */
    public function getNextStepAttribute()
    {
        return $this->workflow->steps()
            ->where('order', '>', $this->order)
            ->first();
    }

    /**
     * Get the previous step in the workflow
     */
    public function getPreviousStepAttribute()
    {
        return $this->workflow->steps()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }
}
