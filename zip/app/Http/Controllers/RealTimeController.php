<?php

namespace App\Http\Controllers;

use App\Models\CollaborationSession;
use App\Models\UserPresence;
use App\Models\File;
use App\Models\RecentActivity;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RealTimeController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Create or join a collaboration session
     */
    public function joinSession(Request $request, File $file): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'session_name' => 'nullable|string|max:255',
            'connection_id' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            // Find existing active session or create new one
            $session = CollaborationSession::forFile($file->id)
                ->active()
                ->first();
            
            if (!$session) {
                $session = CollaborationSession::create([
                    'file_id' => $file->id,
                    'session_name' => $request->session_name ?? 'Collaboration Session',
                    'created_by' => $user->id,
                    'last_activity' => now(),
                    'settings' => [
                        'allow_annotations' => true,
                        'allow_comments' => true,
                        'show_cursors' => true,
                        'max_users' => 10
                    ]
                ]);
            }
            
            // Add user to session
            $presence = $session->addUser($user->id, $request->connection_id);
            
            // Update session activity
            $session->update(['last_activity' => now()]);
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'joined_collaboration_session',
                $file->id,
                $session->id,
                ['session_name' => $session->session_name]
            );
            
            return response()->json([
                'success' => true,
                'session' => $session->toArray(),
                'presence' => $presence->toArray(),
                'message' => 'Joined collaboration session successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to join collaboration session: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to join session'], 500);
        }
    }
    
    /**
     * Leave collaboration session
     */
    public function leaveSession(Request $request, CollaborationSession $session): JsonResponse
    {
        $user = Auth::user();
        
        try {
            // Remove user from session
            $session->removeUser($user->id);
            
            // Update session activity
            $session->update(['last_activity' => now()]);
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'left_collaboration_session',
                $session->file_id,
                $session->id,
                ['session_name' => $session->session_name]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Left collaboration session successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to leave collaboration session: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to leave session'], 500);
        }
    }
    
    /**
     * Update user presence
     */
    public function updatePresence(Request $request, CollaborationSession $session): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string|in:online,away,busy,offline',
            'current_page' => 'nullable|integer|min:1',
            'cursor_position' => 'nullable|array',
            'activity_data' => 'nullable|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            $updateData = [];
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            
            if ($request->has('current_page')) {
                $updateData['current_page'] = $request->current_page;
            }
            
            if ($request->has('cursor_position')) {
                $updateData['cursor_position'] = $request->cursor_position;
            }
            
            if ($request->has('activity_data')) {
                $updateData['activity_data'] = $request->activity_data;
            }
            
            $session->updateUserPresence($user->id, $updateData);
            
            // Update session activity
            $session->update(['last_activity' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Presence updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update presence: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update presence'], 500);
        }
    }
    
    /**
     * Get session participants
     */
    public function getParticipants(CollaborationSession $session): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $session->file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $participants = $session->presences()
                ->with('user')
                ->active()
                ->get()
                ->map(function ($presence) {
                    return $presence->toArray();
                });
            
            return response()->json([
                'success' => true,
                'participants' => $participants,
                'session_stats' => $session->getStats()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get participants: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get participants'], 500);
        }
    }
    
    /**
     * Get real-time updates for session
     */
    public function getUpdates(CollaborationSession $session): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $session->file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            // Get recent annotations
            $recentAnnotations = $session->file->annotations()
                ->where('created_at', '>=', now()->subMinutes(5))
                ->with(['user', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($annotation) {
                    return $annotation->toPdfJsAnnotation();
                });
            
            // Get recent comments
            $recentComments = $session->file->annotations()
                ->with(['comments' => function ($query) {
                    $query->where('created_at', '>=', now()->subMinutes(5))
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                }])
                ->get()
                ->pluck('comments')
                ->flatten()
                ->map(function ($comment) {
                    return $comment->toArray();
                });
            
            // Get active participants
            $participants = $session->presences()
                ->with('user')
                ->active()
                ->get()
                ->map(function ($presence) {
                    return $presence->toArray();
                });
            
            return response()->json([
                'success' => true,
                'updates' => [
                    'recent_annotations' => $recentAnnotations,
                    'recent_comments' => $recentComments,
                    'participants' => $participants,
                    'session_stats' => $session->getStats(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get updates: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get updates'], 500);
        }
    }
    
    /**
     * Broadcast annotation to session participants
     */
    public function broadcastAnnotation(Request $request, CollaborationSession $session): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $session->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'annotation_id' => 'required|integer|exists:document_annotations,id',
            'action' => 'required|string|in:created,updated,deleted,resolved'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            // Update session activity
            $session->update(['last_activity' => now()]);
            
            // Log broadcast activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'broadcasted_annotation',
                $session->file_id,
                $request->annotation_id,
                [
                    'action' => $request->action,
                    'session_id' => $session->session_id
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Annotation broadcasted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to broadcast annotation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to broadcast annotation'], 500);
        }
    }
    
    /**
     * Get active collaboration sessions for a file
     */
    public function getActiveSessions(File $file): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $sessions = CollaborationSession::forFile($file->id)
                ->active()
                ->with(['creator', 'presences.user'])
                ->orderBy('last_activity', 'desc')
                ->get()
                ->map(function ($session) {
                    return $session->toArray();
                });
            
            return response()->json([
                'success' => true,
                'sessions' => $sessions
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get active sessions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get sessions'], 500);
        }
    }
    
    /**
     * Clean up inactive sessions (admin function)
     */
    public function cleanupSessions(): JsonResponse
    {
        $user = Auth::user();
        
        // Only allow admin users to cleanup sessions
        if ($user->type !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $cleanedSessions = CollaborationSession::cleanupInactiveSessions();
            $cleanedPresences = UserPresence::cleanupOldPresences();
            
            return response()->json([
                'success' => true,
                'message' => 'Cleanup completed successfully',
                'cleaned_sessions' => $cleanedSessions,
                'cleaned_presences' => $cleanedPresences
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to cleanup sessions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cleanup sessions'], 500);
        }
    }
}
