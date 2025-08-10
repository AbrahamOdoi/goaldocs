<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowAction;
use App\Models\DocumentTemplate;
use App\Services\WorkflowService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkflowController extends Controller
{
    protected $workflowService;
    protected $permissionService;

    public function __construct(WorkflowService $workflowService, PermissionService $permissionService)
    {
        $this->workflowService = $workflowService;
        $this->permissionService = $permissionService;
    }

    /**
     * Show workflow dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $stats = $this->workflowService->getWorkflowStats();
        $pendingActions = $this->workflowService->getUserPendingActions($user->id);
        
        // Get active workflow instances with their workflows and files
        $activeWorkflows = WorkflowInstance::with(['workflow', 'file', 'initiator'])
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('workflows.dashboard', compact('stats', 'pendingActions', 'activeWorkflows'));
    }

    /**
     * Show workflow list
     */
    public function index()
    {
        $user = Auth::user();
        
        $workflows = Workflow::with(['creator', 'steps'])
            ->when(!$user->is_admin, function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('workflows.index', compact('workflows'));
    }

    /**
     * Show workflow creation form
     */
    public function create()
    {
        return view('workflows.create');
    }

    /**
     * Store new workflow
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return redirect()->back()->withErrors(['error' => 'Access denied']);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:approval,review,notification',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.approver_type' => 'required|in:user,role,manager',
            'steps.*.approver_id' => 'required_if:steps.*.approver_type,user|exists:users,id',
            'steps.*.approver_role' => 'required_if:steps.*.approver_type,role|string',
            'steps.*.timeout_hours' => 'required|integer|min:1|max:168',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $workflow = $this->workflowService->createWorkflow($request->all());
            
            return redirect()->route('workflows.show', $workflow)
                ->with('success', 'Workflow created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create workflow: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show workflow details
     */
    public function show(Workflow $workflow)
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $workflow->created_by !== $user->id) {
            abort(403, 'Access denied');
        }
        
        $stats = $workflow->getStats();
        $instances = $workflow->instances()->with(['file', 'initiator'])->orderBy('created_at', 'desc')->paginate(10);
        
        return view('workflows.show', compact('workflow', 'stats', 'instances'));
    }

    /**
     * Start workflow for a file
     */
    public function startWorkflow(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'workflow_id' => 'required|exists:workflows,id',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $workflow = Workflow::findOrFail($request->workflow_id);
            $instance = $this->workflowService->startWorkflow($file, $workflow, $request->input('data', []));
            
            return response()->json([
                'success' => true,
                'message' => 'Workflow started successfully',
                'instance' => [
                    'id' => $instance->id,
                    'status' => $instance->status,
                    'current_step' => $instance->current_step,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to start workflow: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show workflow instance details
     */
    public function showInstance(WorkflowInstance $instance)
    {
        $user = Auth::user();
        
        // Check if user can view this instance
        if (!$user->is_admin && $instance->initiated_by !== $user->id) {
            $canView = $instance->actions()->where('assigned_to', $user->id)->exists();
            if (!$canView) {
                abort(403, 'Access denied');
            }
        }
        
        $timeline = $instance->getTimeline();
        $currentStepInfo = $instance->getCurrentStepInfo();
        
        return view('workflows.instance', compact('instance', 'timeline', 'currentStepInfo'));
    }

    /**
     * Approve workflow step
     */
    public function approveStep(Request $request, WorkflowInstance $instance)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $success = $this->workflowService->approveStep($instance, $request->input('comment'));
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step approved successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to approve step'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to approve step: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject workflow step
     */
    public function rejectStep(Request $request, WorkflowInstance $instance)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $success = $this->workflowService->rejectStep($instance, $request->input('reason'));
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step rejected successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to reject step'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to reject step: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show pending actions for user
     */
    public function pendingActions()
    {
        $user = Auth::user();
        $pendingActions = $this->workflowService->getUserPendingActions($user->id);
        
        return view('workflows.pending-actions', compact('pendingActions'));
    }

    /**
     * Show document templates
     */
    public function templates()
    {
        $user = Auth::user();
        
        $templates = DocumentTemplate::active()
            ->when(!$user->is_admin, function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('workflows.templates', compact('templates'));
    }

    /**
     * Show template creation form
     */
    public function createTemplate()
    {
        return view('workflows.create-template');
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'variables.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $template = $this->workflowService->createDocumentTemplate($request->all());
            
            return redirect()->route('workflows.templates')
                ->with('success', 'Template created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create template: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show template details
     */
    public function showTemplate(DocumentTemplate $template)
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $template->created_by !== $user->id) {
            abort(403, 'Access denied');
        }
        
        $availableVariables = $template->getAvailableVariables();
        
        return view('workflows.show-template', compact('template', 'availableVariables'));
    }

    /**
     * Generate document from template
     */
    public function generateFromTemplate(Request $request, DocumentTemplate $template)
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $template->created_by !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            // Validate variables
            $errors = $template->validateVariables($request->input('variables', []));
            if (!empty($errors)) {
                return response()->json(['error' => 'Missing required variables', 'errors' => $errors], 400);
            }
            
            $content = $template->generateDocument($request->input('variables', []));
            
            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get workflow statistics
     */
    public function getStats()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $stats = $this->workflowService->getWorkflowStats();
        $trends = $this->workflowService->getTrendsData(9);
        $statusBreakdown = $this->workflowService->getStatusBreakdown();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'trends' => $trends,
            'status' => $statusBreakdown,
        ]);
    }

    /**
     * Cleanup expired actions
     */
    public function cleanupExpiredActions()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $count = $this->workflowService->cleanupExpiredActions();
            
            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$count} expired actions"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cleanup expired actions: ' . $e->getMessage()], 500);
        }
    }
} 