<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Comment;
use App\Models\DocumentLock;
use App\Services\CollaborationService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CollaborationController extends Controller
{
    protected $collaborationService;
    protected $permissionService;

    public function __construct(CollaborationService $collaborationService, PermissionService $permissionService)
    {
        $this->collaborationService = $collaborationService;
        $this->permissionService = $permissionService;
    }

    /**
     * Get comments for a file
     */
    public function getComments(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $comments = $this->collaborationService->getComments($file);
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    /**
     * Add a comment to a file
     */
    public function addComment(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Auto-grant basic permissions if user owns the file or has no permissions
        if ($file->uploaded_by === $user->id || !$this->permissionService->userHasPermission($user, $file, 'view')) {
            $permissions = ['view' => true, 'comment' => true, 'download' => true];
            $this->permissionService->assignPermission($file, $user, $permissions, $user, 'Auto-granted for collaboration');
        }
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'comment')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $comment = $this->collaborationService->addComment(
                $file, 
                $request->input('content'),
                $request->input('parent_id')
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar ?? null,
                        'type' => $comment->user_type,
                    ],
                    'created_at' => $comment->created_at->format('M j, Y g:i A'),
                    'can_edit' => true,
                    'can_delete' => true,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add comment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a comment
     */
    public function updateComment(Request $request, Comment $comment)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->collaborationService->canEditComment($comment)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $success = $this->collaborationService->updateComment($comment, $request->input('content'));
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment updated successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to update comment'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update comment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Comment $comment)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->collaborationService->canDeleteComment($comment)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $success = $this->collaborationService->deleteComment($comment);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment deleted successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to delete comment'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete comment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lock a document
     */
    public function lockDocument(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $duration = $request->input('duration', 30);
        
        try {
            $lock = $this->collaborationService->lockDocument($file, $duration);
            
            if ($lock) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document locked successfully',
                    'lock' => [
                        'id' => $lock->id,
                        'expires_at' => $lock->expires_at->format('M j, Y g:i A'),
                        'remaining_minutes' => now()->diffInMinutes($lock->expires_at, false),
                    ]
                ]);
            } else {
                return response()->json(['error' => 'Document is already locked by another user'], 409);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to lock document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unlock a document
     */
    public function unlockDocument(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $success = $this->collaborationService->unlockDocument($file);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document unlocked successfully'
                ]);
            } else {
                return response()->json(['error' => 'Document is not locked by you'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to unlock document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get document lock status
     */
    public function getDocumentLock(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $lock = $this->collaborationService->getDocumentLock($file);
        
        return response()->json([
            'success' => true,
            'lock' => $lock
        ]);
    }

    /**
     * Force unlock a document (admin only)
     */
    public function forceUnlockDocument(File $file)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $success = $this->collaborationService->forceUnlockDocument($file);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document force unlocked successfully'
                ]);
            } else {
                return response()->json(['error' => 'Document is not locked'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to force unlock document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get active users viewing a document
     */
    public function getActiveUsers(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $activeUsers = $this->collaborationService->getActiveUsers($file);
        
        return response()->json([
            'success' => true,
            'active_users' => $activeUsers
        ]);
    }

    /**
     * Update user activity
     */
    public function updateActivity(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $this->collaborationService->updateUserActivity($file);
        
        return response()->json([
            'success' => true,
            'message' => 'Activity updated'
        ]);
    }

    /**
     * Get collaboration statistics
     */
    public function getCollaborationStats(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $stats = $this->collaborationService->getCollaborationStats($file);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get collaboration feed
     */
    public function getCollaborationFeed(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $feed = $this->collaborationService->getCollaborationFeed($file);
        
        return response()->json([
            'success' => true,
            'feed' => $feed
        ]);
    }

    /**
     * Show collaboration interface
     */
    public function showCollaboration(File $file)
    {
        $user = Auth::user();
        
        // Auto-grant basic permissions if user owns the file or has no permissions
        if ($file->uploaded_by === $user->id || !$this->permissionService->userHasPermission($user, $file, 'view')) {
            $permissions = ['view' => true, 'comment' => true, 'download' => true];
            $this->permissionService->assignPermission($file, $user, $permissions, $user, 'Auto-granted for collaboration');
        }
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $comments = $this->collaborationService->getComments($file);
        $lock = $this->collaborationService->getDocumentLock($file);
        $activeUsers = $this->collaborationService->getActiveUsers($file);
        $stats = $this->collaborationService->getCollaborationStats($file);
        $feed = $this->collaborationService->getCollaborationFeed($file);
        
        return view('files.collaboration', compact('file', 'comments', 'lock', 'activeUsers', 'stats', 'feed'));
    }

    /**
     * AJAX endpoint for collaboration operations
     */
    public function ajaxCollaboration(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $operation = $request->get('operation');
        
        switch ($operation) {
            case 'comments':
                return $this->getComments($file);
                
            case 'active_users':
                return $this->getActiveUsers($file);
                
            case 'lock_status':
                return $this->getDocumentLock($file);
                
            case 'stats':
                return $this->getCollaborationStats($file);
                
            case 'feed':
                return $this->getCollaborationFeed($file);
                
            case 'update_activity':
                return $this->updateActivity($file);
                
            default:
                return response()->json(['error' => 'Invalid operation'], 400);
        }
    }

    /**
     * Cleanup expired locks (admin only)
     */
    public function cleanupExpiredLocks()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $count = $this->collaborationService->cleanupExpiredLocks();
            
            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$count} expired locks"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cleanup expired locks: ' . $e->getMessage()], 500);
        }
    }
} 