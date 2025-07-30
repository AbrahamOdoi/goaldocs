<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'folder_id',
        'content',
        'type',
        'metadata',
        'author_id',
        'user_type',
        'type_name',
        'parent_comment_id',
        'thread_level',
        'status',
        'is_private',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_private' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected $dates = [
        'resolved_at',
    ];

    /**
     * Get the file this comment belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the folder this comment belongs to
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the author of this comment
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent comment (for threading)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentComment::class, 'parent_comment_id');
    }

    /**
     * Get the replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DocumentComment::class, 'parent_comment_id');
    }

    /**
     * Get the user who resolved this comment
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for active comments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for resolved comments
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for comments by organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)
                    ->where('type_name', $typeName);
    }

    /**
     * Scope for comments by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for public comments (not private)
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope for comments visible to a specific user
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('is_private', false)
              ->orWhere('author_id', $user->id)
              ->orWhere('user_type', $user->type)
              ->where('type_name', $user->type_name);
        });
    }

    /**
     * Get the document this comment belongs to
     */
    public function getDocumentAttribute()
    {
        return $this->file ?? $this->folder;
    }

    /**
     * Check if comment is resolved
     */
    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if comment is a reply
     */
    public function getIsReplyAttribute(): bool
    {
        return !is_null($this->parent_comment_id);
    }

    /**
     * Check if comment is a top-level comment
     */
    public function getIsTopLevelAttribute(): bool
    {
        return is_null($this->parent_comment_id);
    }

    /**
     * Get the comment type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return ucfirst($this->type);
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Resolve this comment
     */
    public function resolve(User $resolvedBy, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $resolvedBy->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Reopen this comment
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'active',
            'resolved_by' => null,
            'resolved_at' => null,
            'resolution_notes' => null,
        ]);
    }

    /**
     * Archive this comment
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Make this comment private
     */
    public function makePrivate(): void
    {
        $this->update(['is_private' => true]);
    }

    /**
     * Make this comment public
     */
    public function makePublic(): void
    {
        $this->update(['is_private' => false]);
    }
}
