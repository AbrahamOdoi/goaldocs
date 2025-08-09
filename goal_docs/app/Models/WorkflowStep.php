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
        'step_order',
        'approver_type',
        'approver_id',
        'approver_role',
        'is_required',
        'timeout_hours',
        'actions',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'timeout_hours' => 'integer',
        'actions' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow this step belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the actions for this step
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }

    /**
     * Get the approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope to get steps by order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('step_order');
    }

    /**
     * Check if step is overdue
     */
    public function isOverdue(): bool
    {
        $pendingAction = $this->actions()
            ->where('action_type', 'pending')
            ->where('due_date', '<', now())
            ->first();

        return $pendingAction !== null;
    }

    /**
     * Get step status
     */
    public function getStatus(): string
    {
        $pendingAction = $this->actions()
            ->where('action_type', 'pending')
            ->first();

        if ($pendingAction) {
            if ($pendingAction->due_date->isPast()) {
                return 'overdue';
            }
            return 'pending';
        }

        $approvedAction = $this->actions()
            ->where('action_type', 'approved')
            ->first();

        if ($approvedAction) {
            return 'approved';
        }

        $rejectedAction = $this->actions()
            ->where('action_type', 'rejected')
            ->first();

        if ($rejectedAction) {
            return 'rejected';
        }

        return 'not_started';
    }
}
