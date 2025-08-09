<?php

namespace App\Services;

use App\Models\File;
use App\Models\Comment;
use App\Models\DocumentLock;
use App\Models\RecentActivity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CollaborationService
{
    /**
     * Add a comment to a file
     */
    public function addComment(File $file, string $content, ?string $parentId = null): Comment
    {
        $user = Auth::user();
        
        $comment = Comment::create([
            'commentable_type' => File::class,
            'commentable_id' => $file->id,
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_type_name' => $user->type_name,
            'content' => $content,
            'parent_id' => $parentId,
            'metadata' => [
                'file_name' => $file->name,
                'file_path' => $file->file_path,
                'comment_type' => 'file_comment',
                'created_at' => now()->toISOString(),
            ]
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'comment_add',
            $file->id,
            $comment->id,
            [
                'comment_content' => Str::limit($content, 100),
                'file_name' => $file->name,
            ]
        );
        
        // Clear cache
        $this->clearCommentCache($file->id);
        
        return $comment->load('user', 'replies');
    }
    
    /**
     * Get comments for a file
     */
    public function getComments(File $file, bool $includeReplies = true): array
    {
        $cacheKey = "file_comments_{$file->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($file, $includeReplies) {
            $query = Comment::where('commentable_type', File::class)
                ->where('commentable_id', $file->id)
                ->whereNull('parent_id')
                ->with(['user', 'replies.user'])
                ->orderBy('created_at', 'desc');
            
            if ($includeReplies) {
                $query->with(['replies' => function ($q) {
                    $q->orderBy('created_at', 'asc');
                }]);
            }
            
            $comments = $query->get();
            
            return $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar ?? null,
                        'type' => $comment->user_type,
                    ],
                    'created_at' => $comment->created_at->format('M j, Y g:i A'),
                    'updated_at' => $comment->updated_at->format('M j, Y g:i A'),
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                                'avatar' => $reply->user->avatar ?? null,
                                'type' => $reply->user_type,
                            ],
                            'created_at' => $reply->created_at->format('M j, Y g:i A'),
                            'updated_at' => $reply->updated_at->format('M j, Y g:i A'),
                        ];
                    })->toArray(),
                    'can_edit' => $this->canEditComment($comment),
                    'can_delete' => $this->canDeleteComment($comment),
                ];
            })->toArray();
        });
    }
    
    /**
     * Update a comment
     */
    public function updateComment(Comment $comment, string $content): bool
    {
        $user = Auth::user();
        
        if (!$this->canEditComment($comment)) {
            return false;
        }
        
        $comment->update([
            'content' => $content,
            'metadata' => array_merge($comment->metadata ?? [], [
                'edited_at' => now()->toISOString(),
                'edited_by' => $user->id,
            ])
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'comment_edit',
            $comment->commentable_id,
            $comment->id,
            [
                'comment_content' => Str::limit($content, 100),
            ]
        );
        
        // Clear cache
        $this->clearCommentCache($comment->commentable_id);
        
        return true;
    }
    
    /**
     * Delete a comment
     */
    public function deleteComment(Comment $comment): bool
    {
        $user = Auth::user();
        
        if (!$this->canDeleteComment($comment)) {
            return false;
        }
        
        // Delete replies first
        $comment->replies()->delete();
        
        // Delete the comment
        $comment->delete();
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'comment_delete',
            $comment->commentable_id,
            null,
            [
                'comment_content' => Str::limit($comment->content, 100),
            ]
        );
        
        // Clear cache
        $this->clearCommentCache($comment->commentable_id);
        
        return true;
    }
    
    /**
     * Lock a document for editing
     */
    public function lockDocument(File $file, int $duration = 30): ?DocumentLock
    {
        $user = Auth::user();
        
        // Check if document is already locked
        $existingLock = DocumentLock::where('file_id', $file->id)
            ->where('expires_at', '>', now())
            ->first();
        
        if ($existingLock) {
            // Check if it's locked by the same user
            if ($existingLock->user_id === $user->id) {
                // Extend the lock
                $existingLock->update([
                    'expires_at' => now()->addMinutes($duration),
                ]);
                return $existingLock;
            } else {
                // Document is locked by another user
                return null;
            }
        }
        
        // Create new lock
        $lock = DocumentLock::create([
            'file_id' => $file->id,
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_type_name' => $user->type_name,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes($duration),
            'metadata' => [
                'file_name' => $file->name,
                'lock_reason' => 'Document editing',
                'session_id' => session()->getId(),
            ]
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'document_lock',
            $file->id,
            $lock->id,
            [
                'file_name' => $file->name,
                'lock_duration' => $duration,
            ]
        );
        
        return $lock;
    }
    
    /**
     * Unlock a document
     */
    public function unlockDocument(File $file): bool
    {
        $user = Auth::user();
        
        $lock = DocumentLock::where('file_id', $file->id)
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$lock) {
            return false;
        }
        
        $lock->delete();
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'document_unlock',
            $file->id,
            null,
            [
                'file_name' => $file->name,
            ]
        );
        
        return true;
    }
    
    /**
     * Get document lock status
     */
    public function getDocumentLock(File $file): ?array
    {
        $lock = DocumentLock::where('file_id', $file->id)
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();
        
        if (!$lock) {
            return null;
        }
        
        return [
            'id' => $lock->id,
            'user' => [
                'id' => $lock->user->id,
                'name' => $lock->user->name,
                'type' => $lock->user_type,
            ],
            'locked_at' => $lock->locked_at->format('M j, Y g:i A'),
            'expires_at' => $lock->expires_at->format('M j, Y g:i A'),
            'remaining_minutes' => now()->diffInMinutes($lock->expires_at, false),
            'is_own_lock' => $lock->user_id === Auth::id(),
        ];
    }
    
    /**
     * Force unlock a document (admin only)
     */
    public function forceUnlockDocument(File $file): bool
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return false;
        }
        
        $lock = DocumentLock::where('file_id', $file->id)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$lock) {
            return false;
        }
        
        $lock->delete();
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'document_force_unlock',
            $file->id,
            null,
            [
                'file_name' => $file->name,
                'force_unlock' => true,
            ]
        );
        
        return true;
    }
    
    /**
     * Get active users viewing a document
     */
    public function getActiveUsers(File $file): array
    {
        $cacheKey = "active_users_file_{$file->id}";
        
        return Cache::remember($cacheKey, 30, function () use ($file) {
            // Get users who have viewed the file in the last 5 minutes
            $recentActivity = RecentActivity::where('resource_id', $file->id)
                ->where('action', 'view')
                ->where('created_at', '>', now()->subMinutes(5))
                ->with('user')
                ->get()
                ->unique('user_id');
            
            return $recentActivity->map(function ($activity) {
                return [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'avatar' => $activity->user->avatar ?? null,
                    'type' => $activity->user_type,
                    'last_seen' => $activity->created_at->format('g:i A'),
                ];
            })->toArray();
        });
    }
    
    /**
     * Update user activity for a document
     */
    public function updateUserActivity(File $file): void
    {
        $user = Auth::user();
        
        // Log view activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'view',
            $file->id,
            null,
            [
                'file_name' => $file->name,
                'session_id' => session()->getId(),
            ]
        );
        
        // Update active users cache
        $this->clearActiveUsersCache($file->id);
    }
    
    /**
     * Get collaboration statistics
     */
    public function getCollaborationStats(File $file): array
    {
        $stats = [
            'total_comments' => Comment::where('commentable_type', File::class)
                ->where('commentable_id', $file->id)
                ->count(),
            'total_replies' => Comment::where('commentable_type', File::class)
                ->where('commentable_id', $file->id)
                ->whereNotNull('parent_id')
                ->count(),
            'unique_commenters' => Comment::where('commentable_type', File::class)
                ->where('commentable_id', $file->id)
                ->distinct('user_id')
                ->count(),
            'recent_activity' => RecentActivity::where('resource_id', $file->id)
                ->where('created_at', '>', now()->subDays(7))
                ->count(),
            'lock_history' => DocumentLock::where('file_id', $file->id)
                ->where('created_at', '>', now()->subDays(30))
                ->count(),
        ];
        
        return $stats;
    }
    
    /**
     * Check if user can edit a comment
     */
    public function canEditComment(Comment $comment): bool
    {
        $user = Auth::user();
        
        // Comment owner can edit
        if ($comment->user_id === $user->id) {
            return true;
        }
        
        // Admins can edit any comment
        if ($user->is_admin) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can delete a comment
     */
    public function canDeleteComment(Comment $comment): bool
    {
        $user = Auth::user();
        
        // Comment owner can delete
        if ($comment->user_id === $user->id) {
            return true;
        }
        
        // Admins can delete any comment
        if ($user->is_admin) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear comment cache
     */
    private function clearCommentCache(int $fileId): void
    {
        Cache::forget("file_comments_{$fileId}");
    }
    
    /**
     * Clear active users cache
     */
    private function clearActiveUsersCache(int $fileId): void
    {
        Cache::forget("active_users_file_{$fileId}");
    }
    
    /**
     * Clean up expired locks
     */
    public function cleanupExpiredLocks(): int
    {
        $expiredLocks = DocumentLock::where('expires_at', '<', now())->get();
        $count = $expiredLocks->count();
        
        foreach ($expiredLocks as $lock) {
            $lock->delete();
        }
        
        return $count;
    }
    
    /**
     * Get collaboration feed for a file
     */
    public function getCollaborationFeed(File $file, int $limit = 20): array
    {
        $activities = RecentActivity::where('resource_id', $file->id)
            ->whereIn('action', ['comment_add', 'comment_edit', 'comment_delete', 'document_lock', 'document_unlock', 'view'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'avatar' => $activity->user->avatar ?? null,
                    'type' => $activity->user_type,
                ],
                'timestamp' => $activity->created_at->format('M j, Y g:i A'),
                'metadata' => $activity->metadata,
                'description' => $this->getActivityDescription($activity),
            ];
        })->toArray();
    }
    
    /**
     * Get human-readable activity description
     */
    private function getActivityDescription($activity): string
    {
        switch ($activity->action) {
            case 'comment_add':
                return 'added a comment';
            case 'comment_edit':
                return 'edited a comment';
            case 'comment_delete':
                return 'deleted a comment';
            case 'document_lock':
                return 'locked the document';
            case 'document_unlock':
                return 'unlocked the document';
            case 'view':
                return 'viewed the document';
            default:
                return 'performed an action';
        }
    }
} 