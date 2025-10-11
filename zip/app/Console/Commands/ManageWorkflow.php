<?php

namespace App\Console\Commands;

use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowAction;
use App\Models\DocumentTemplate;
use App\Services\WorkflowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:manage {action} {--workflow-id= : Specific workflow ID} {--older-than=30 : Clean up data older than X days} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage workflow features - cleanup, statistics, and maintenance';

    /**
     * Execute the console command.
     */
    public function handle(WorkflowService $workflowService)
    {
        $action = $this->argument('action');
        $workflowId = $this->option('workflow-id');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');
        
        switch ($action) {
            case 'stats':
                $this->showWorkflowStats($workflowId, $workflowService);
                break;
                
            case 'cleanup':
                $this->cleanupWorkflowData($workflowId, $olderThan, $dryRun);
                break;
                
            case 'actions':
                $this->manageActions($dryRun, $workflowService);
                break;
                
            case 'templates':
                $this->manageTemplates($dryRun);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: stats, cleanup, actions, templates");
                return 1;
        }
        
        return 0;
    }
    
    /**
     * Show workflow statistics
     */
    private function showWorkflowStats($workflowId = null, WorkflowService $workflowService)
    {
        $this->info('Workflow Statistics');
        $this->info('==================');
        
        if ($workflowId) {
            $workflow = Workflow::find($workflowId);
            if (!$workflow) {
                $this->error("Workflow with ID {$workflowId} not found");
                return;
            }
            
            $stats = $workflow->getStats();
            $this->info("Workflow: {$workflow->name}");
            $this->info("Type: {$workflow->type}");
            $this->info("Total instances: {$stats['total_instances']}");
            $this->info("Active instances: {$stats['active_instances']}");
            $this->info("Completed instances: {$stats['completed_instances']}");
            $this->info("Rejected instances: {$stats['rejected_instances']}");
            $this->info("Average completion time: {$stats['average_completion_time']} hours");
            
            // Show steps
            $steps = $workflow->steps()->ordered()->get();
            if ($steps->count() > 0) {
                $this->newLine();
                $this->info('Workflow Steps:');
                foreach ($steps as $step) {
                    $this->info("  - Step {$step->step_order}: {$step->name} (Approver: {$step->approver_type})");
                }
            }
        } else {
            $stats = $workflowService->getWorkflowStats();
            $this->info("Total workflows: {$stats['total_workflows']}");
            $this->info("Active workflows: {$stats['active_workflows']}");
            $this->info("Total instances: {$stats['total_instances']}");
            $this->info("Active instances: {$stats['active_instances']}");
            $this->info("Completed instances: {$stats['completed_instances']}");
            $this->info("Rejected instances: {$stats['rejected_instances']}");
            $this->info("Pending actions: {$stats['pending_actions']}");
            $this->info("Overdue actions: {$stats['overdue_actions']}");
            
            // Top workflows by activity
            $topWorkflows = Workflow::withCount('instances')
                ->orderBy('instances_count', 'desc')
                ->limit(10)
                ->get();
                
            if ($topWorkflows->count() > 0) {
                $this->newLine();
                $this->info('Top workflows by activity:');
                foreach ($topWorkflows as $workflow) {
                    $this->info("  - {$workflow->name}: {$workflow->instances_count} instances");
                }
            }
        }
    }
    
    /**
     * Cleanup workflow data
     */
    private function cleanupWorkflowData($workflowId, $olderThan, $dryRun)
    {
        $this->info('Cleaning up workflow data...');
        
        $query = WorkflowInstance::query();
        
        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }
        
        // Find old completed/rejected instances
        $oldInstances = $query->whereIn('status', ['completed', 'rejected'])
            ->where('created_at', '<', now()->subDays($olderThan))
            ->get();
        
        $deletedInstances = 0;
        $deletedActions = 0;
        
        foreach ($oldInstances as $instance) {
            if ($dryRun) {
                $this->info("Would delete workflow instance {$instance->id} from {$instance->created_at->format('Y-m-d')}");
            } else {
                try {
                    // Delete associated actions first
                    $actionCount = $instance->actions()->count();
                    $instance->actions()->delete();
                    $deletedActions += $actionCount;
                    
                    // Delete the instance
                    $instance->delete();
                    $deletedInstances++;
                    
                    $this->info("Deleted workflow instance {$instance->id} and {$actionCount} actions");
                } catch (\Exception $e) {
                    $this->error("Failed to delete workflow instance {$instance->id}: " . $e->getMessage());
                }
            }
        }
        
        if ($dryRun) {
            $this->info("Dry run completed. Would delete {$deletedInstances} workflow instances and {$deletedActions} actions.");
        } else {
            $this->info("Cleanup completed. Deleted {$deletedInstances} workflow instances and {$deletedActions} actions.");
        }
    }
    
    /**
     * Manage workflow actions
     */
    private function manageActions($dryRun, WorkflowService $workflowService)
    {
        $this->info('Managing workflow actions...');
        
        // Clean up expired actions
        $expiredActions = WorkflowAction::where('action_type', 'pending')
            ->where('due_date', '<', now()->subDays(7))
            ->get();
        $expiredCount = $expiredActions->count();
        
        if ($dryRun) {
            $this->info("Would clean up {$expiredCount} expired actions");
        } else {
            $count = $workflowService->cleanupExpiredActions();
            $this->info("Cleaned up {$count} expired actions");
        }
        
        // Show pending actions
        $pendingActions = WorkflowAction::where('action_type', 'pending')
            ->with(['workflowInstance.file', 'assignedTo', 'step'])
            ->orderBy('due_date', 'asc')
            ->get();
        
        if ($pendingActions->count() > 0) {
            $this->newLine();
            $this->info('Pending workflow actions:');
            foreach ($pendingActions as $action) {
                $status = $action->isOverdue() ? 'OVERDUE' : 'Pending';
                $this->info("  - {$action->workflowInstance->file->name}: {$action->step->name} (Assigned to: {$action->assignedTo->name}, Due: {$action->due_date->format('Y-m-d H:i')}) [{$status}]");
            }
        } else {
            $this->info('No pending workflow actions');
        }
        
        // Show overdue actions
        $overdueActions = WorkflowAction::where('action_type', 'pending')
            ->where('due_date', '<', now())
            ->count();
        
        if ($overdueActions > 0) {
            $this->warn("There are {$overdueActions} overdue actions that need attention");
        }
    }
    
    /**
     * Manage document templates
     */
    private function manageTemplates($dryRun)
    {
        $this->info('Managing document templates...');
        
        $templates = DocumentTemplate::with('creator')->get();
        
        if ($templates->count() > 0) {
            $this->info('Document Templates:');
            foreach ($templates as $template) {
                $status = $template->is_active ? 'Active' : 'Inactive';
                $this->info("  - {$template->name} ({$template->category}) - {$status} - Created by: {$template->creator->name}");
            }
        } else {
            $this->info('No document templates found');
        }
        
        // Show template statistics
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();
        $inactiveTemplates = DocumentTemplate::where('is_active', false)->count();
        
        $this->newLine();
        $this->info('Template Statistics:');
        $this->info("Total templates: {$totalTemplates}");
        $this->info("Active templates: {$activeTemplates}");
        $this->info("Inactive templates: {$inactiveTemplates}");
        
        // Show templates by category
        $templatesByCategory = DocumentTemplate::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
            
        if ($templatesByCategory->count() > 0) {
            $this->newLine();
            $this->info('Templates by category:');
            foreach ($templatesByCategory as $category) {
                $this->info("  - {$category->category}: {$category->count}");
            }
        }
    }
}
