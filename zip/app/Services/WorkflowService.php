<?php

namespace App\Services;

use App\Models\File;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowInstance;
use App\Models\WorkflowAction;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkflowService
{
    /**
     * Create a new workflow
     */
    public function createWorkflow(array $data): Workflow
    {
        $user = Auth::user();
        
        $workflow = Workflow::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'approval',
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $user->id,
            'metadata' => $data['metadata'] ?? [],
        ]);
        
        // Create workflow steps
        if (isset($data['steps']) && is_array($data['steps'])) {
            foreach ($data['steps'] as $index => $stepData) {
                WorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'name' => $stepData['name'],
                    'description' => $stepData['description'] ?? null,
                    'step_order' => $index + 1,
                    'approver_type' => $stepData['approver_type'] ?? 'user',
                    'approver_id' => $stepData['approver_id'] ?? null,
                    'approver_role' => $stepData['approver_role'] ?? null,
                    'is_required' => $stepData['is_required'] ?? true,
                    'timeout_hours' => $stepData['timeout_hours'] ?? 24,
                    'actions' => $stepData['actions'] ?? [],
                    'metadata' => $stepData['metadata'] ?? [],
                ]);
            }
        }
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'workflow_create',
            $workflow->id,
            null,
            [
                'workflow_name' => $workflow->name,
                'workflow_type' => $workflow->type,
            ]
        );
        
        return $workflow->load('steps');
    }
    
    /**
     * Start a workflow for a file
     */
    public function startWorkflow(File $file, Workflow $workflow, array $data = []): WorkflowInstance
    {
        $user = Auth::user();
        
        // Check if workflow is active
        if (!$workflow->is_active) {
            throw new \Exception('Workflow is not active');
        }
        
        // Check if file already has an active workflow
        $activeInstance = WorkflowInstance::where('file_id', $file->id)
            ->where('status', 'in_progress')
            ->first();
            
        if ($activeInstance) {
            throw new \Exception('File already has an active workflow');
        }
        
        // Create workflow instance
        $instance = WorkflowInstance::create([
            'workflow_id' => $workflow->id,
            'file_id' => $file->id,
            'initiated_by' => $user->id,
            'status' => 'in_progress',
            'current_step' => 1,
            'data' => $data,
            'metadata' => [
                'initiated_at' => now()->toISOString(),
                'workflow_name' => $workflow->name,
                'file_name' => $file->name,
            ]
        ]);
        
        // Get first step
        $firstStep = $workflow->steps()->where('step_order', 1)->first();
        
        if ($firstStep) {
            // Create action for first step
            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'step_id' => $firstStep->id,
                'action_type' => 'pending',
                'assigned_to' => $this->getApproverId($firstStep),
                'due_date' => now()->addHours($firstStep->timeout_hours),
                'metadata' => [
                    'step_name' => $firstStep->name,
                    'step_order' => $firstStep->step_order,
                ]
            ]);
        }
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'workflow_start',
            $file->id,
            $instance->id,
            [
                'workflow_name' => $workflow->name,
                'file_name' => $file->name,
            ]
        );
        
        return $instance->load(['workflow', 'currentAction', 'actions']);
    }
    
    /**
     * Approve a workflow step
     */
    public function approveStep(WorkflowInstance $instance, string $comment = null): bool
    {
        $user = Auth::user();
        
        // Check if user can approve current step
        $currentAction = $instance->currentAction;
        if (!$currentAction || $currentAction->assigned_to !== $user->id) {
            throw new \Exception('You are not authorized to approve this step');
        }
        
        // Mark current action as approved
        $currentAction->update([
            'action_type' => 'approved',
            'acted_by' => $user->id,
            'acted_at' => now(),
            'comment' => $comment,
        ]);
        
        // Move to next step or complete workflow
        $this->moveToNextStep($instance);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'workflow_approve',
            $instance->file_id,
            $instance->id,
            [
                'step_name' => $currentAction->step->name,
                'comment' => $comment,
            ]
        );
        
        return true;
    }
    
    /**
     * Reject a workflow step
     */
    public function rejectStep(WorkflowInstance $instance, string $reason = null): bool
    {
        $user = Auth::user();
        
        // Check if user can reject current step
        $currentAction = $instance->currentAction;
        if (!$currentAction || $currentAction->assigned_to !== $user->id) {
            throw new \Exception('You are not authorized to reject this step');
        }
        
        // Mark current action as rejected
        $currentAction->update([
            'action_type' => 'rejected',
            'acted_by' => $user->id,
            'acted_at' => now(),
            'comment' => $reason,
        ]);
        
        // Mark workflow as rejected
        $instance->update([
            'status' => 'rejected',
            'completed_at' => now(),
            'metadata' => array_merge($instance->metadata ?? [], [
                'rejected_at' => now()->toISOString(),
                'rejected_by' => $user->id,
                'rejection_reason' => $reason,
            ])
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'workflow_reject',
            $instance->file_id,
            $instance->id,
            [
                'step_name' => $currentAction->step->name,
                'reason' => $reason,
            ]
        );
        
        return true;
    }
    
    /**
     * Move to next step in workflow
     */
    private function moveToNextStep(WorkflowInstance $instance): void
    {
        $workflow = $instance->workflow;
        $nextStepOrder = $instance->current_step + 1;
        
        // Check if there's a next step
        $nextStep = $workflow->steps()->where('step_order', $nextStepOrder)->first();
        
        if ($nextStep) {
            // Move to next step
            $instance->update(['current_step' => $nextStepOrder]);
            
            // Create action for next step
            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'step_id' => $nextStep->id,
                'action_type' => 'pending',
                'assigned_to' => $this->getApproverId($nextStep),
                'due_date' => now()->addHours($nextStep->timeout_hours),
                'metadata' => [
                    'step_name' => $nextStep->name,
                    'step_order' => $nextStep->step_order,
                ]
            ]);
        } else {
            // Workflow completed
            $instance->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge($instance->metadata ?? [], [
                    'completed_at' => now()->toISOString(),
                ])
            ]);
            
            // Execute completion actions
            $this->executeCompletionActions($instance);
        }
    }
    
    /**
     * Get approver ID for a step
     */
    private function getApproverId(WorkflowStep $step): ?int
    {
        switch ($step->approver_type) {
            case 'user':
                return $step->approver_id;
                
            case 'role':
                // Get user with specific role
                return User::where('role', $step->approver_role)->first()->id ?? null;
                
            case 'manager':
                // Get user's manager
                $currentUser = Auth::user();
                return $currentUser->manager_id ?? null;
                
            default:
                return null;
        }
    }
    
    /**
     * Execute completion actions
     */
    private function executeCompletionActions(WorkflowInstance $instance): void
    {
        $workflow = $instance->workflow;
        $file = $instance->file;
        
        // Execute workflow completion actions
        if (isset($workflow->metadata['completion_actions'])) {
            foreach ($workflow->metadata['completion_actions'] as $action) {
                $this->executeAction($action, $file, $instance);
            }
        }
        
        // Log completion
        RecentActivity::log(
            $instance->initiated_by,
            'user',
            'User',
            'workflow_complete',
            $file->id,
            $instance->id,
            [
                'workflow_name' => $workflow->name,
                'file_name' => $file->name,
            ]
        );
    }
    
    /**
     * Execute a workflow action
     */
    private function executeAction(array $action, File $file, WorkflowInstance $instance): void
    {
        switch ($action['type']) {
            case 'notify':
                $this->sendNotification($action, $file, $instance);
                break;
                
            case 'move_file':
                $this->moveFile($action, $file);
                break;
                
            case 'update_metadata':
                $this->updateFileMetadata($action, $file);
                break;
                
            case 'create_version':
                $this->createFileVersion($action, $file);
                break;
                
            case 'send_email':
                $this->sendEmail($action, $file, $instance);
                break;
        }
    }
    
    /**
     * Send notification
     */
    private function sendNotification(array $action, File $file, WorkflowInstance $instance): void
    {
        // Implementation for sending notifications
        Log::info("Sending notification for workflow action", [
            'action' => $action,
            'file_id' => $file->id,
            'instance_id' => $instance->id,
        ]);
    }
    
    /**
     * Move file
     */
    private function moveFile(array $action, File $file): void
    {
        if (isset($action['destination_folder'])) {
            // Implementation for moving file to different folder
            Log::info("Moving file to destination", [
                'file_id' => $file->id,
                'destination' => $action['destination_folder'],
            ]);
        }
    }
    
    /**
     * Update file metadata
     */
    private function updateFileMetadata(array $action, File $file): void
    {
        if (isset($action['metadata'])) {
            $file->update([
                'metadata' => array_merge($file->metadata ?? [], $action['metadata'])
            ]);
        }
    }
    
    /**
     * Create file version
     */
    private function createFileVersion(array $action, File $file): void
    {
        // Implementation for creating file version
        Log::info("Creating file version for workflow", [
            'file_id' => $file->id,
            'action' => $action,
        ]);
    }
    
    /**
     * Send email
     */
    private function sendEmail(array $action, File $file, WorkflowInstance $instance): void
    {
        // Implementation for sending emails
        Log::info("Sending email for workflow", [
            'action' => $action,
            'file_id' => $file->id,
            'instance_id' => $instance->id,
        ]);
    }
    
    /**
     * Get workflow statistics
     */
    public function getWorkflowStats(): array
    {
        $stats = [
            'total_workflows' => Workflow::count(),
            'active_workflows' => Workflow::where('is_active', true)->count(),
            'total_instances' => WorkflowInstance::count(),
            'active_instances' => WorkflowInstance::where('status', 'in_progress')->count(),
            'completed_instances' => WorkflowInstance::where('status', 'completed')->count(),
            'rejected_instances' => WorkflowInstance::where('status', 'rejected')->count(),
            'pending_actions' => WorkflowAction::where('action_type', 'pending')->count(),
            'overdue_actions' => WorkflowAction::where('action_type', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];
        // Derived metrics
        $stats['success_rate'] = $stats['total_instances'] > 0
            ? round(($stats['completed_instances'] / $stats['total_instances']) * 100)
            : 0;

        return $stats;
    }

    /**
     * Get monthly trends for completed instances and pending actions
     */
    public function getTrendsData(int $months = 9): array
    {
        $months = max(1, min($months, 24));
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        // Prepare month buckets
        $categories = [];
        $monthKeys = [];
        for ($i = 0; $i < $months; $i++) {
            $date = (clone $start)->addMonths($i);
            $key = $date->format('Y-m');
            $monthKeys[] = $key;
            $categories[] = $date->format('M');
        }

        $completedCounts = array_fill(0, $months, 0);
        $pendingCounts = array_fill(0, $months, 0);

        // Fetch completed instances within range
        $completed = WorkflowInstance::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $start)
            ->get(['completed_at']);

        foreach ($completed as $instance) {
            $key = Carbon::parse($instance->completed_at)->format('Y-m');
            $index = array_search($key, $monthKeys, true);
            if ($index !== false) {
                $completedCounts[$index]++;
            }
        }

        // Fetch pending actions created within range (as a proxy for pending trend)
        $pendings = WorkflowAction::where('action_type', 'pending')
            ->where('created_at', '>=', $start)
            ->get(['created_at']);

        foreach ($pendings as $action) {
            $key = Carbon::parse($action->created_at)->format('Y-m');
            $index = array_search($key, $monthKeys, true);
            if ($index !== false) {
                $pendingCounts[$index]++;
            }
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Completed',
                    'data' => $completedCounts,
                ],
                [
                    'name' => 'Pending',
                    'data' => $pendingCounts,
                ],
            ],
        ];
    }

    /**
     * Get status breakdown for donut chart
     */
    public function getStatusBreakdown(): array
    {
        $active = WorkflowInstance::where('status', 'in_progress')->count();
        $pending = WorkflowAction::where('action_type', 'pending')->count();
        $completed = WorkflowInstance::where('status', 'completed')->count();

        return [
            'labels' => ['Active', 'Pending', 'Completed'],
            'series' => [$active, $pending, $completed],
        ];
    }
    
    /**
     * Get user's pending workflow actions
     */
    public function getUserPendingActions(int $userId): array
    {
        $actions = WorkflowAction::where('assigned_to', $userId)
            ->where('action_type', 'pending')
            ->with(['workflowInstance.file', 'step'])
            ->orderBy('due_date', 'asc')
            ->get();
            
        return $actions->map(function ($action) {
            return [
                'id' => $action->id,
                'workflow_name' => $action->workflowInstance->workflow->name,
                'file_name' => $action->workflowInstance->file->name,
                'step_name' => $action->step->name,
                'due_date' => $action->due_date->format('M j, Y g:i A'),
                'is_overdue' => $action->due_date->isPast(),
                'instance_id' => $action->workflow_instance_id,
            ];
        })->toArray();
    }
    
    /**
     * Create document template
     */
    public function createDocumentTemplate(array $data): DocumentTemplate
    {
        $user = Auth::user();
        
        $template = DocumentTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'general',
            'content' => $data['content'],
            'variables' => $data['variables'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $user->id,
            'metadata' => $data['metadata'] ?? [],
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'template_create',
            $template->id,
            null,
            [
                'template_name' => $template->name,
                'category' => $template->category,
            ]
        );
        
        return $template;
    }
    
    /**
     * Generate document from template
     */
    public function generateDocumentFromTemplate(DocumentTemplate $template, array $variables = []): string
    {
        $content = $template->content;
        
        // Replace variables in template
        foreach ($variables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        // Add default variables
        $defaultVariables = [
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'user_name' => Auth::user()->name,
            'user_email' => Auth::user()->email,
        ];
        
        foreach ($defaultVariables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Clean up expired workflow actions
     */
    public function cleanupExpiredActions(): int
    {
        $expiredActions = WorkflowAction::where('action_type', 'pending')
            ->where('due_date', '<', now()->subDays(7))
            ->get();
            
        $count = $expiredActions->count();
        
        foreach ($expiredActions as $action) {
            // Mark as expired
            $action->update([
                'action_type' => 'expired',
                'metadata' => array_merge($action->metadata ?? [], [
                    'expired_at' => now()->toISOString(),
                ])
            ]);
        }
        
        return $count;
    }
} 