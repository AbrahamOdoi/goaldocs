<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the steps for this workflow
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }

    /**
     * Get the instances of this workflow
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Get the user who created this workflow
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active workflows
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get workflows by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get workflow statistics
     */
    public function getStats(): array
    {
        return [
            'total_instances' => $this->instances()->count(),
            'active_instances' => $this->instances()->where('status', 'in_progress')->count(),
            'completed_instances' => $this->instances()->where('status', 'completed')->count(),
            'rejected_instances' => $this->instances()->where('status', 'rejected')->count(),
            'average_completion_time' => $this->getAverageCompletionTime(),
        ];
    }

    /**
     * Get average completion time in hours
     */
    private function getAverageCompletionTime(): float
    {
        $completedInstances = $this->instances()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        if ($completedInstances->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($completedInstances as $instance) {
            $totalHours += $instance->created_at->diffInHours($instance->completed_at);
        }

        return round($totalHours / $completedInstances->count(), 2);
    }
}
