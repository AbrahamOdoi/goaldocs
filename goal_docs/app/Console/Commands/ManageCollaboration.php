<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\DocumentLock;
use App\Models\File;
use App\Models\RecentActivity;
use App\Services\CollaborationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageCollaboration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collaboration:manage {action} {--file-id= : Specific file ID} {--older-than=30 : Clean up data older than X days} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage collaboration features - cleanup, statistics, and maintenance';

    /**
     * Execute the console command.
     */
    public function handle(CollaborationService $collaborationService)
    {
        $action = $this->argument('action');
        $fileId = $this->option('file-id');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');
        
        switch ($action) {
            case 'stats':
                $this->showCollaborationStats($fileId);
                break;
                
            case 'cleanup':
                $this->cleanupCollaborationData($fileId, $olderThan, $dryRun);
                break;
                
            case 'locks':
                $this->manageLocks($dryRun);
                break;
                
            case 'activity':
                $this->cleanupActivity($olderThan, $dryRun);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: stats, cleanup, locks, activity");
                return 1;
        }
        
        return 0;
    }
    
    /**
     * Show collaboration statistics
     */
    private function showCollaborationStats($fileId = null)
    {
        $this->info('Collaboration Statistics');
        $this->info('======================');
        
        if ($fileId) {
            $file = File::find($fileId);
            if (!$file) {
                $this->error("File with ID {$fileId} not found");
                return;
            }
            
            $collaborationService = app(CollaborationService::class);
            $stats = $collaborationService->getCollaborationStats($file);
            $this->info("File: {$file->name}");
            $this->info("Total comments: {$stats['total_comments']}");
            $this->info("Total replies: {$stats['total_replies']}");
            $this->info("Unique commenters: {$stats['unique_commenters']}");
            $this->info("Recent activity (7 days): {$stats['recent_activity']}");
            $this->info("Lock history (30 days): {$stats['lock_history']}");
        } else {
            $totalComments = Comment::count();
            $totalReplies = Comment::whereNotNull('parent_id')->count();
            $totalLocks = DocumentLock::count();
            $activeLocks = DocumentLock::where('expires_at', '>', now())->count();
            $totalActivity = RecentActivity::count();
            
            $this->info("Total comments: {$totalComments}");
            $this->info("Total replies: {$totalReplies}");
            $this->info("Total document locks: {$totalLocks}");
            $this->info("Active locks: {$activeLocks}");
            $this->info("Total activity records: {$totalActivity}");
            
            // Top files by collaboration
            $topFiles = File::withCount(['comments', 'documentLocks'])
                ->orderBy('comments_count', 'desc')
                ->limit(10)
                ->get();
                
            if ($topFiles->count() > 0) {
                $this->newLine();
                $this->info('Top files by collaboration:');
                foreach ($topFiles as $file) {
                    $this->info("  - {$file->name}: {$file->comments_count} comments, {$file->document_locks_count} locks");
                }
            }
        }
    }
    
    /**
     * Cleanup collaboration data
     */
    private function cleanupCollaborationData($fileId, $olderThan, $dryRun)
    {
        $this->info('Cleaning up collaboration data...');
        
        $query = Comment::query();
        
        if ($fileId) {
            $query->where('commentable_id', $fileId);
        }
        
        // Find old comments
        $oldComments = $query->where('created_at', '<', now()->subDays($olderThan))->get();
        
        $deletedComments = 0;
        $deletedReplies = 0;
        
        foreach ($oldComments as $comment) {
            if ($dryRun) {
                $this->info("Would delete comment {$comment->id} from {$comment->created_at->format('Y-m-d')}");
            } else {
                try {
                    // Delete replies first
                    $replyCount = $comment->replies()->count();
                    $comment->replies()->delete();
                    $deletedReplies += $replyCount;
                    
                    // Delete the comment
                    $comment->delete();
                    $deletedComments++;
                    
                    $this->info("Deleted comment {$comment->id} and {$replyCount} replies");
                } catch (\Exception $e) {
                    $this->error("Failed to delete comment {$comment->id}: " . $e->getMessage());
                }
            }
        }
        
        if ($dryRun) {
            $this->info("Dry run completed. Would delete {$deletedComments} comments and {$deletedReplies} replies.");
        } else {
            $this->info("Cleanup completed. Deleted {$deletedComments} comments and {$deletedReplies} replies.");
        }
    }
    
    /**
     * Manage document locks
     */
    private function manageLocks($dryRun)
    {
        $this->info('Managing document locks...');
        
        // Clean up expired locks
        $expiredLocks = DocumentLock::where('expires_at', '<', now())->get();
        $expiredCount = $expiredLocks->count();
        
        if ($dryRun) {
            $this->info("Would clean up {$expiredCount} expired locks");
        } else {
            foreach ($expiredLocks as $lock) {
                try {
                    $lock->delete();
                    $this->info("Deleted expired lock {$lock->id} for file {$lock->file_id}");
                } catch (\Exception $e) {
                    $this->error("Failed to delete lock {$lock->id}: " . $e->getMessage());
                }
            }
            $this->info("Cleaned up {$expiredCount} expired locks");
        }
        
        // Show active locks
        $activeLocks = DocumentLock::where('expires_at', '>', now())->with(['file', 'user'])->get();
        
        if ($activeLocks->count() > 0) {
            $this->newLine();
            $this->info('Active document locks:');
            foreach ($activeLocks as $lock) {
                $remaining = $lock->getRemainingMinutes();
                $this->info("  - File: {$lock->file->name}, User: {$lock->user->name}, Remaining: {$remaining} minutes");
            }
        } else {
            $this->info('No active document locks');
        }
    }
    
    /**
     * Cleanup activity data
     */
    private function cleanupActivity($olderThan, $dryRun)
    {
        $this->info('Cleaning up activity data...');
        
        // Find old activity records
        $oldActivity = RecentActivity::where('created_at', '<', now()->subDays($olderThan))->get();
        $count = $oldActivity->count();
        
        if ($dryRun) {
            $this->info("Would delete {$count} old activity records");
        } else {
            foreach ($oldActivity as $activity) {
                try {
                    $activity->delete();
                } catch (\Exception $e) {
                    $this->error("Failed to delete activity {$activity->id}: " . $e->getMessage());
                }
            }
            $this->info("Deleted {$count} old activity records");
        }
        
        // Show activity statistics
        $totalActivity = RecentActivity::count();
        $recentActivity = RecentActivity::where('created_at', '>', now()->subDays(7))->count();
        
        $this->newLine();
        $this->info('Activity Statistics:');
        $this->info("Total activity records: {$totalActivity}");
        $this->info("Recent activity (7 days): {$recentActivity}");
        
        // Show activity by type
        $activityByType = RecentActivity::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
            
        if ($activityByType->count() > 0) {
            $this->newLine();
            $this->info('Activity by type:');
            foreach ($activityByType as $activity) {
                $this->info("  - {$activity->action}: {$activity->count}");
            }
        }
    }
}
