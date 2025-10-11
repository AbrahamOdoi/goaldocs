<?php

namespace App\Http\Controllers;

use App\Models\DocumentWorkflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowAssignment;
use App\Models\File;
use App\Models\Folder;
use App\Models\RecentActivity;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DocumentWorkflowController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Get workflows for a file or folder
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'nullable|in:file,folder',
            'resource_id' => 'nullable|integer',
            'status' => 'nullable|in:draft,active,completed,cancelled',
            'type' => 'nullable|in:approval,review,custom',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $query = DocumentWorkflow::query();

        // Filter by resource if specified
        if ($request->resource_type && $request->resource_id) {
            $resource = null;
            if ($request->resource_type === 'file') {
                $resource = File::find($request->resource_id);
            } else {
                $resource = Folder::find($request->resource_id);
            }

            if (!$resource) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            // Check if user has permission to view this resource
            if (!$this->permissionService->userHasPermission($user, $resource, 'view')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $query->where($request->resource_type . '_id', $request->resource_id);
        }

        // Filter by organization
        if ($user->type === 'individual') {
            $query->forUserType($user->type);
        } else {
            $query->forOrganization($user->type, $user->type_name);
        }

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Get workflows with relationships
        $workflows = $query->with(['creator', 'assignee', 'steps.assignments.assignable'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'workflows' => $workflows,
        ]);
    }

    /**
     * Store a new workflow
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:approval,review,custom',
            'config' => 'nullable|array',
            'is_template' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after:now',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.description' => 'nullable|string|max:500',
            'steps.*.type' => 'required|in:approval,review,notification,action',
            'steps.*.assignee_type' => 'required|in:user,position,department,any',
            'steps.*.assignee_id' => 'nullable|integer',
            'steps.*.assignee_name' => 'nullable|string|max:255',
            'steps.*.timeout_hours' => 'nullable|integer|min:1',
            'steps.*.due_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user has permission to manage this resource
        if (!$this->permissionService->userHasPermission($user, $resource, 'manage')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        DB::beginTransaction();
        try {
            // Create workflow
            $workflow = DocumentWorkflow::create([
                $request->resource_type . '_id' => $request->resource_id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'config' => $request->config,
                'is_template' => $request->is_template ?? false,
                'user_type' => $user->type,
                'type_name' => $user->type_name,
                'created_by' => $user->id,
                'assigned_to' => $request->assigned_to,
                'due_date' => $request->due_date,
            ]);

            // Create workflow steps
            foreach ($request->steps as $index => $stepData) {
                $step = WorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'name' => $stepData['name'],
                    'description' => $stepData['description'] ?? null,
                    'order' => $index + 1,
                    'type' => $stepData['type'],
                    'assignee_type' => $stepData['assignee_type'],
                    'assignee_id' => $stepData['assignee_id'] ?? null,
                    'assignee_name' => $stepData['assignee_name'] ?? null,
                    'timeout_hours' => $stepData['timeout_hours'] ?? null,
                    'due_date' => $stepData['due_date'] ?? null,
                ]);

                // Create assignments for the step
                if ($stepData['assignee_type'] === 'user' && $stepData['assignee_id']) {
                    WorkflowAssignment::create([
                        'workflow_id' => $workflow->id,
                        'step_id' => $step->id,
                        'assignable_type' => 'App\Models\User',
                        'assignable_id' => $stepData['assignee_id'],
                        'role' => 'primary',
                        'assigned_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'create_workflow',
                $request->resource_type === 'file' ? $resource->id : null,
                $request->resource_type === 'folder' ? $resource->id : null,
                ['workflow_name' => $workflow->name, 'workflow_type' => $workflow->type]
            );

            return response()->json([
                'success' => true,
                'workflow' => $workflow->load(['creator', 'assignee', 'steps.assignments.assignable']),
                'message' => 'Workflow created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create workflow: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Start a workflow
     */
    public function start(DocumentWorkflow $workflow): JsonResponse
    {
        $user = Auth::user();

        // Check if user can start this workflow
        if ($workflow->created_by !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if ($workflow->status !== 'draft') {
            return response()->json(['error' => 'Workflow is not in draft status'], 400);
        }

        $workflow->start();

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'start_workflow',
            $workflow->file_id,
            $workflow->folder_id,
            ['workflow_name' => $workflow->name]
        );

        return response()->json([
            'success' => true,
            'workflow' => $workflow->load(['creator', 'assignee', 'steps.assignments.assignable']),
            'message' => 'Workflow started successfully'
        ]);
    }

    /**
     * Complete a workflow step
     */
    public function completeStep(Request $request, WorkflowStep $step): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected,requested_changes,delegated',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        // Check if user can complete this step
        $assignment = $step->assignments()
            ->where('assignable_type', 'App\Models\User')
            ->where('assignable_id', $user->id)
            ->first();

        if (!$assignment && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if ($step->status !== 'active') {
            return response()->json(['error' => 'Step is not active'], 400);
        }

        DB::beginTransaction();
        try {
            // Complete the step
            $step->complete($request->action, $request->notes, $user);

            // Update assignment
            if ($assignment) {
                $assignment->complete($request->action, $request->notes, $request->metadata);
            }

            // Advance workflow to next step
            $workflow = $step->workflow;
            $advanced = $workflow->advanceToNextStep();

            DB::commit();

            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'complete_workflow_step',
                $workflow->file_id,
                $workflow->folder_id,
                [
                    'workflow_name' => $workflow->name,
                    'step_name' => $step->name,
                    'action' => $request->action
                ]
            );

            return response()->json([
                'success' => true,
                'step' => $step->load(['assignments.assignable']),
                'workflow' => $workflow->load(['creator', 'assignee', 'steps.assignments.assignable']),
                'advanced' => $advanced,
                'message' => 'Step completed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to complete step: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delegate a workflow assignment
     */
    public function delegateAssignment(Request $request, WorkflowAssignment $assignment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delegated_to' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        // Check if user can delegate this assignment
        if ($assignment->assignable_type === 'App\Models\User' && 
            $assignment->assignable_id !== $user->id && 
            !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $delegateTo = User::find($request->delegated_to);
        $assignment->delegate($delegateTo, $request->reason);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'delegate_workflow',
            $assignment->workflow->file_id,
            $assignment->workflow->folder_id,
            [
                'workflow_name' => $assignment->workflow->name,
                'delegated_to' => $delegateTo->full_name
            ]
        );

        return response()->json([
            'success' => true,
            'assignment' => $assignment->load(['delegatedTo']),
            'message' => 'Assignment delegated successfully'
        ]);
    }

    /**
     * Cancel a workflow
     */
    public function cancel(DocumentWorkflow $workflow): JsonResponse
    {
        $user = Auth::user();

        // Check if user can cancel this workflow
        if ($workflow->created_by !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if ($workflow->status === 'completed') {
            return response()->json(['error' => 'Cannot cancel completed workflow'], 400);
        }

        $workflow->cancel();

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'cancel_workflow',
            $workflow->file_id,
            $workflow->folder_id,
            ['workflow_name' => $workflow->name]
        );

        return response()->json([
            'success' => true,
            'workflow' => $workflow->load(['creator', 'assignee', 'steps.assignments.assignable']),
            'message' => 'Workflow cancelled successfully'
        ]);
    }

    /**
     * Get workflow statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = DocumentWorkflow::query();

        // Filter by organization
        if ($user->type === 'individual') {
            $query->forUserType($user->type);
        } else {
            $query->forOrganization($user->type, $user->type_name);
        }

        $statistics = [
            'total' => $query->count(),
            'draft' => $query->where('status', 'draft')->count(),
            'active' => $query->where('status', 'active')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
            'overdue' => $query->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get user's workflow assignments
     */
    public function myAssignments(): JsonResponse
    {
        $user = Auth::user();
        
        $assignments = WorkflowAssignment::forUser($user)
            ->with(['workflow.creator', 'step', 'assignable'])
            ->whereHas('workflow', function($q) use ($user) {
                if ($user->type === 'individual') {
                    $q->forUserType($user->type);
                } else {
                    $q->forOrganization($user->type, $user->type_name);
                }
            })
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'assignments' => $assignments,
        ]);
    }
}
