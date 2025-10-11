<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'file_id',
        'initiated_by',
        'status',
        'current_step',
        'data',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'data' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the workflow this instance belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the file this workflow is for
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who initiated this workflow
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the actions for this workflow instance
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }

    /**
     * Get the current action
     */
    public function currentAction(): BelongsTo
    {
        return $this->belongsTo(WorkflowAction::class, 'current_action_id');
    }

    /**
     * Scope to get only active instances
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get only completed instances
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get only rejected instances
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if workflow is active
     */
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if workflow is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if workflow is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get workflow duration in hours
     */
    public function getDurationHours(): int
    {
        if (!$this->completed_at) {
            return $this->created_at->diffInHours(now());
        }

        return $this->created_at->diffInHours($this->completed_at);
    }

    /**
     * Get current step information
     */
    public function getCurrentStepInfo(): ?array
    {
        $currentStep = $this->workflow->steps()
            ->where('step_order', $this->current_step)
            ->first();

        if (!$currentStep) {
            return null;
        }

        return [
            'id' => $currentStep->id,
            'name' => $currentStep->name,
            'description' => $currentStep->description,
            'step_order' => $currentStep->step_order,
            'approver_type' => $currentStep->approver_type,
            'approver_id' => $currentStep->approver_id,
            'timeout_hours' => $currentStep->timeout_hours,
        ];
    }

    /**
     * Get workflow progress percentage
     */
    public function getProgressPercentage(): int
    {
        $totalSteps = $this->workflow->steps()->count();
        if ($totalSteps === 0) {
            return 0;
        }

        return round(($this->current_step - 1) / $totalSteps * 100);
    }

    /**
     * Get workflow timeline
     */
    public function getTimeline(): array
    {
        $timeline = [];
        $actions = $this->actions()->with(['step', 'actedBy'])->orderBy('created_at')->get();

        foreach ($actions as $action) {
            $timeline[] = [
                'id' => $action->id,
                'step_name' => $action->step->name,
                'action_type' => $action->action_type,
                'acted_by' => $action->actedBy ? $action->actedBy->name : null,
                'acted_at' => $action->acted_at ? $action->acted_at->format('M j, Y g:i A') : null,
                'comment' => $action->comment,
                'due_date' => $action->due_date->format('M j, Y g:i A'),
                'is_overdue' => $action->due_date->isPast() && $action->action_type === 'pending',
            ];
        }

        return $timeline;
    }
}
