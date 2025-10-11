<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'folder_id',
        'name',
        'description',
        'type',
        'status',
        'config',
        'is_template',
        'user_type',
        'type_name',
        'created_by',
        'assigned_to',
        'started_at',
        'completed_at',
        'due_date',
    ];

    protected $casts = [
        'config' => 'array',
        'is_template' => 'boolean',
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
     * Get the file this workflow belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the folder this workflow belongs to
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the creator of this workflow
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the primary assignee of this workflow
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the steps in this workflow
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_id')->orderBy('order');
    }

    /**
     * Get the active step in this workflow
     */
    public function activeStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'active_step_id');
    }

    /**
     * Get the document this workflow belongs to
     */
    public function getDocumentAttribute()
    {
        return $this->file ?? $this->folder;
    }

    /**
     * Scope for active workflows
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed workflows
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for draft workflows
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for workflows by organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)
                    ->where('type_name', $typeName);
    }

    /**
     * Scope for workflows by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for workflow templates
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Scope for workflows assigned to a user
     */
    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id);
    }

    /**
     * Scope for workflows created by a user
     */
    public function scopeCreatedBy($query, User $user)
    {
        return $query->where('created_by', $user->id);
    }

    /**
     * Check if workflow is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if workflow is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if workflow is draft
     */
    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if workflow is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted;
    }

    /**
     * Get the workflow type display name
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
     * Start this workflow
     */
    public function start(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        // Activate the first step
        $firstStep = $this->steps()->first();
        if ($firstStep) {
            $firstStep->update(['status' => 'active']);
        }
    }

    /**
     * Complete this workflow
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel this workflow
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get the current step
     */
    public function getCurrentStepAttribute()
    {
        return $this->steps()->where('status', 'active')->first();
    }

    /**
     * Get the next step
     */
    public function getNextStepAttribute()
    {
        $currentStep = $this->currentStep;
        if (!$currentStep) {
            return $this->steps()->first();
        }

        return $this->steps()
            ->where('order', '>', $currentStep->order)
            ->first();
    }

    /**
     * Get the previous step
     */
    public function getPreviousStepAttribute()
    {
        $currentStep = $this->currentStep;
        if (!$currentStep) {
            return null;
        }

        return $this->steps()
            ->where('order', '<', $currentStep->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Advance to the next step
     */
    public function advanceToNextStep(): bool
    {
        $currentStep = $this->currentStep;
        if (!$currentStep) {
            return false;
        }

        // Mark current step as completed
        $currentStep->update(['status' => 'completed']);

        // Get next step
        $nextStep = $this->nextStep;
        if ($nextStep) {
            $nextStep->update(['status' => 'active']);
            return true;
        } else {
            // No more steps, complete the workflow
            $this->complete();
            return false;
        }
    }

    /**
     * Get workflow progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        $totalSteps = $this->steps()->count();
        if ($totalSteps === 0) {
            return 0;
        }

        $completedSteps = $this->steps()->where('status', 'completed')->count();
        return round(($completedSteps / $totalSteps) * 100);
    }
}
