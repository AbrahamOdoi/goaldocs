<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class AnnotationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'annotation_id',
        'user_id',
        'parent_id',
        'content',
        'mentions',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'metadata'
    ];

    protected $casts = [
        'mentions' => 'array',
        'metadata' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

    /**
     * Get the annotation that owns the comment
     */
    public function annotation(): BelongsTo
    {
        return $this->belongsTo(DocumentAnnotation::class, 'annotation_id');
    }

    /**
     * Get the user who created the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the comment
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the parent comment (for threaded comments)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AnnotationComment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(AnnotationComment::class, 'parent_id');
    }

    /**
     * Get all replies recursively
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(AnnotationComment::class, 'parent_id')->with('replies');
    }

    /**
     * Scope for unresolved comments
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved comments
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope for top-level comments (no parent)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for replies (has parent)
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Check if comment has replies
     */
    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }

    /**
     * Check if comment is a reply
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if comment is a top-level comment
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Get mentioned users
     */
    public function getMentionedUsers()
    {
        if (!$this->mentions) {
            return collect();
        }

        return User::whereIn('id', $this->mentions)->get();
    }

    /**
     * Parse mentions from content
     */
    public function parseMentions(): array
    {
        preg_match_all('/@(\w+)/', $this->content, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Resolve the comment
     */
    public function resolve($userId = null): bool
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId ?? Auth::id()
        ]);

        return true;
    }

    /**
     * Unresolve the comment
     */
    public function unresolve(): bool
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null
        ]);

        return true;
    }

    /**
     * Get comment data for frontend
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        $data['user_name'] = $this->user->name;
        $data['user_avatar'] = $this->user->avatar ?? null;
        $data['has_replies'] = $this->hasReplies();
        $data['is_reply'] = $this->isReply();
        $data['replies_count'] = $this->replies()->count();
        $data['mentioned_users'] = $this->getMentionedUsers()->pluck('name')->toArray();
        
        return $data;
    }
}
