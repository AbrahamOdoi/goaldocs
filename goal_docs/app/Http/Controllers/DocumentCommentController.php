<?php

namespace App\Http\Controllers;

use App\Models\DocumentComment;
use App\Models\File;
use App\Models\Folder;
use App\Models\RecentActivity;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DocumentCommentController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Get comments for a file or folder
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
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

        // Check if user has permission to view this resource
        if (!$this->permissionService->userHasPermission($user, $resource, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Get comments visible to the user
        $comments = DocumentComment::visibleTo($user)
            ->where($request->resource_type . '_id', $request->resource_id)
            ->with(['author', 'replies.author', 'resolvedBy'])
            ->whereNull('parent_comment_id') // Only top-level comments
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'content' => 'required|string|max:2000',
            'type' => 'nullable|in:comment,annotation,suggestion',
            'metadata' => 'nullable|array',
            'parent_comment_id' => 'nullable|exists:document_comments,id',
            'is_private' => 'nullable|boolean',
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

        // Check if user has permission to view this resource
        if (!$this->permissionService->userHasPermission($user, $resource, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Calculate thread level
        $threadLevel = 0;
        if ($request->parent_comment_id) {
            $parentComment = DocumentComment::find($request->parent_comment_id);
            if (!$parentComment) {
                return response()->json(['error' => 'Parent comment not found'], 404);
            }
            $threadLevel = $parentComment->thread_level + 1;
        }

        try {
            $comment = DocumentComment::create([
                $request->resource_type . '_id' => $request->resource_id,
                'content' => $request->content,
                'type' => $request->type ?? 'comment',
                'metadata' => $request->metadata,
                'author_id' => $user->id,
                'user_type' => $user->type,
                'type_name' => $user->type_name,
                'parent_comment_id' => $request->parent_comment_id,
                'thread_level' => $threadLevel,
                'is_private' => $request->is_private ?? false,
            ]);

            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'comment',
                $request->resource_type === 'file' ? $resource->id : null,
                $request->resource_type === 'folder' ? $resource->id : null,
                ['comment_type' => $comment->type, 'is_private' => $comment->is_private]
            );

            return response()->json([
                'success' => true,
                'comment' => $comment->load(['author', 'parent']),
                'message' => 'Comment added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create comment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a comment
     */
    public function update(Request $request, DocumentComment $comment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'is_private' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        // Check if user can edit this comment
        if ($comment->author_id !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'You can only edit your own comments'], 403);
        }

        $comment->update([
            'content' => $request->content,
            'is_private' => $request->is_private ?? $comment->is_private,
        ]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit_comment',
            $comment->file_id,
            $comment->folder_id,
            ['comment_id' => $comment->id]
        );

        return response()->json([
            'success' => true,
            'comment' => $comment->load(['author']),
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Resolve a comment
     */
    public function resolve(Request $request, DocumentComment $comment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        // Check if user can resolve this comment
        if ($comment->author_id !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comment->resolve($user, $request->resolution_notes);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'resolve_comment',
            $comment->file_id,
            $comment->folder_id,
            ['comment_id' => $comment->id]
        );

        return response()->json([
            'success' => true,
            'comment' => $comment->load(['author', 'resolvedBy']),
            'message' => 'Comment resolved successfully'
        ]);
    }

    /**
     * Reopen a comment
     */
    public function reopen(DocumentComment $comment): JsonResponse
    {
        $user = Auth::user();

        // Check if user can reopen this comment
        if ($comment->author_id !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comment->reopen();

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'reopen_comment',
            $comment->file_id,
            $comment->folder_id,
            ['comment_id' => $comment->id]
        );

        return response()->json([
            'success' => true,
            'comment' => $comment->load(['author']),
            'message' => 'Comment reopened successfully'
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy(DocumentComment $comment): JsonResponse
    {
        $user = Auth::user();

        // Check if user can delete this comment
        if ($comment->author_id !== $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Log activity before deletion
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'delete_comment',
            $comment->file_id,
            $comment->folder_id,
            ['comment_id' => $comment->id, 'content' => substr($comment->content, 0, 100)]
        );

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Get comment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
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

        // Check if user has permission to view this resource
        if (!$this->permissionService->userHasPermission($user, $resource, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comments = DocumentComment::visibleTo($user)
            ->where($request->resource_type . '_id', $request->resource_id);

        $statistics = [
            'total' => $comments->count(),
            'active' => $comments->where('status', 'active')->count(),
            'resolved' => $comments->where('status', 'resolved')->count(),
            'private' => $comments->where('is_private', true)->count(),
            'public' => $comments->where('is_private', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }
}
