<?php

namespace App\Http\Controllers;

use App\Models\DocumentAnnotation;
use App\Models\AnnotationComment;
use App\Models\File;
use App\Models\RecentActivity;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AnnotationController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Get annotations for a file
     */
    public function getAnnotations(File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user has permission to view this file
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $annotations = DocumentAnnotation::where('file_id', $file->id)
            ->with(['user', 'comments.user', 'resolvedBy'])
            ->orderBy('page_number')
            ->orderBy('created_at')
            ->get()
            ->map(function ($annotation) {
                return $annotation->toPdfJsAnnotation();
            });
        
        return response()->json([
            'success' => true,
            'annotations' => $annotations
        ]);
    }
    
    /**
     * Get annotations for a specific page
     */
    public function getPageAnnotations(File $file, int $pageNumber): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $annotations = DocumentAnnotation::where('file_id', $file->id)
            ->where('page_number', $pageNumber)
            ->with(['user', 'comments.user', 'resolvedBy'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($annotation) {
                return $annotation->toPdfJsAnnotation();
            });
        
        return response()->json([
            'success' => true,
            'annotations' => $annotations
        ]);
    }
    
    /**
     * Create a new annotation
     */
    public function store(Request $request, File $file): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'annotation_type' => 'required|string|in:' . implode(',', array_keys(DocumentAnnotation::getAnnotationTypes())),
            'page_number' => 'required|integer|min:1',
            'coordinates' => 'required|array',
            'content' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'opacity' => 'nullable|numeric|between:0,1',
            'style' => 'nullable|array',
            'metadata' => 'nullable|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            $annotation = DocumentAnnotation::create([
                'file_id' => $file->id,
                'user_id' => $user->id,
                'annotation_type' => $request->annotation_type,
                'page_number' => $request->page_number,
                'coordinates' => $request->coordinates,
                'content' => $request->content,
                'color' => $request->color ?? DocumentAnnotation::getDefaultColors()[$request->annotation_type] ?? '#FFEB3B',
                'opacity' => $request->opacity ?? 1.0,
                'style' => $request->style,
                'metadata' => $request->metadata
            ]);
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'annotation_created',
                $file->id,
                $annotation->id,
                [
                    'file_name' => $file->original_name,
                    'annotation_type' => $request->annotation_type,
                    'page_number' => $request->page_number
                ]
            );
            
            return response()->json([
                'success' => true,
                'annotation' => $annotation->toPdfJsAnnotation(),
                'message' => 'Annotation created successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Annotation creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create annotation'], 500);
        }
    }
    
    /**
     * Update an annotation
     */
    public function update(Request $request, DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user owns the annotation or has edit permission on the file
        if ($annotation->user_id !== $user->id && 
            !$this->permissionService->userHasPermission($user, $annotation->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'opacity' => 'nullable|numeric|between:0,1',
            'style' => 'nullable|array',
            'metadata' => 'nullable|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            $annotation->update($request->only(['content', 'color', 'opacity', 'style', 'metadata']));
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'annotation_updated',
                $annotation->file_id,
                $annotation->id,
                ['annotation_type' => $annotation->annotation_type]
            );
            
            return response()->json([
                'success' => true,
                'annotation' => $annotation->toPdfJsAnnotation(),
                'message' => 'Annotation updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Annotation update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update annotation'], 500);
        }
    }
    
    /**
     * Delete an annotation
     */
    public function destroy(DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user owns the annotation or has edit permission on the file
        if ($annotation->user_id !== $user->id && 
            !$this->permissionService->userHasPermission($user, $annotation->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $annotationData = $annotation->toPdfJsAnnotation();
            $annotation->delete();
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'annotation_deleted',
                $annotation->file_id,
                null,
                [
                    'annotation_type' => $annotationData['type'],
                    'page_number' => $annotationData['pageNumber']
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Annotation deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Annotation deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete annotation'], 500);
        }
    }
    
    /**
     * Resolve an annotation
     */
    public function resolve(DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $annotation->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $annotation->resolve($user->id);
            
            return response()->json([
                'success' => true,
                'annotation' => $annotation->toPdfJsAnnotation(),
                'message' => 'Annotation resolved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Annotation resolution failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to resolve annotation'], 500);
        }
    }
    
    /**
     * Unresolve an annotation
     */
    public function unresolve(DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $annotation->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $annotation->unresolve();
            
            return response()->json([
                'success' => true,
                'annotation' => $annotation->toPdfJsAnnotation(),
                'message' => 'Annotation unresolved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Annotation unresolution failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to unresolve annotation'], 500);
        }
    }
    
    /**
     * Get comments for an annotation
     */
    public function getComments(DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $annotation->file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $comments = $annotation->comments()
            ->with(['user', 'replies.user'])
            ->topLevel()
            ->orderBy('created_at')
            ->get();
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }
    
    /**
     * Add a comment to an annotation
     */
    public function addComment(Request $request, DocumentAnnotation $annotation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->permissionService->userHasPermission($user, $annotation->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:annotation_comments,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            $comment = AnnotationComment::create([
                'annotation_id' => $annotation->id,
                'user_id' => $user->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
                'mentions' => $this->parseMentions($request->content)
            ]);
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'annotation_comment_added',
                $annotation->file_id,
                $annotation->id,
                ['comment_length' => strlen($request->content)]
            );
            
            return response()->json([
                'success' => true,
                'comment' => $comment->toArray(),
                'message' => 'Comment added successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Comment creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add comment'], 500);
        }
    }
    
    /**
     * Update a comment
     */
    public function updateComment(Request $request, AnnotationComment $comment): JsonResponse
    {
        $user = Auth::user();
        
        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        try {
            $comment->update([
                'content' => $request->content,
                'mentions' => $this->parseMentions($request->content)
            ]);
            
            return response()->json([
                'success' => true,
                'comment' => $comment->toArray(),
                'message' => 'Comment updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Comment update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update comment'], 500);
        }
    }
    
    /**
     * Delete a comment
     */
    public function deleteComment(AnnotationComment $comment): JsonResponse
    {
        $user = Auth::user();
        
        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $comment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Comment deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete comment'], 500);
        }
    }
    
    /**
     * Parse mentions from content
     */
    private function parseMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        return $matches[1] ?? [];
    }
}
