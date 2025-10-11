<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DocumentAnnotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'annotation_type',
        'page_number',
        'coordinates',
        'content',
        'style',
        'color',
        'opacity',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'metadata'
    ];

    protected $casts = [
        'coordinates' => 'array',
        'style' => 'array',
        'metadata' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'opacity' => 'decimal:2'
    ];

    // Annotation types
    const TYPE_HIGHLIGHT = 'highlight';
    const TYPE_UNDERLINE = 'underline';
    const TYPE_STRIKEOUT = 'strikeout';
    const TYPE_TEXT = 'text';
    const TYPE_DRAWING = 'drawing';
    const TYPE_SHAPE = 'shape';
    const TYPE_STICKY_NOTE = 'sticky_note';
    const TYPE_ARROW = 'arrow';
    const TYPE_RECTANGLE = 'rectangle';
    const TYPE_CIRCLE = 'circle';

    /**
     * Get the file that owns the annotation
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created the annotation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the annotation
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the comments for this annotation
     */
    public function comments(): HasMany
    {
        return $this->hasMany(AnnotationComment::class, 'annotation_id');
    }

    /**
     * Get unresolved comments for this annotation
     */
    public function unresolvedComments(): HasMany
    {
        return $this->hasMany(AnnotationComment::class, 'annotation_id')->where('is_resolved', false);
    }

    /**
     * Scope for unresolved annotations
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved annotations
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope for annotations by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('annotation_type', $type);
    }

    /**
     * Scope for annotations on a specific page
     */
    public function scopeOnPage($query, $pageNumber)
    {
        return $query->where('page_number', $pageNumber);
    }

    /**
     * Get annotation type options
     */
    public static function getAnnotationTypes(): array
    {
        return [
            self::TYPE_HIGHLIGHT => 'Highlight',
            self::TYPE_UNDERLINE => 'Underline',
            self::TYPE_STRIKEOUT => 'Strikeout',
            self::TYPE_TEXT => 'Text',
            self::TYPE_DRAWING => 'Drawing',
            self::TYPE_SHAPE => 'Shape',
            self::TYPE_STICKY_NOTE => 'Sticky Note',
            self::TYPE_ARROW => 'Arrow',
            self::TYPE_RECTANGLE => 'Rectangle',
            self::TYPE_CIRCLE => 'Circle'
        ];
    }

    /**
     * Get default colors for annotation types
     */
    public static function getDefaultColors(): array
    {
        return [
            self::TYPE_HIGHLIGHT => '#FFEB3B',
            self::TYPE_UNDERLINE => '#2196F3',
            self::TYPE_STRIKEOUT => '#F44336',
            self::TYPE_TEXT => '#4CAF50',
            self::TYPE_DRAWING => '#FF9800',
            self::TYPE_SHAPE => '#9C27B0',
            self::TYPE_STICKY_NOTE => '#FFEB3B',
            self::TYPE_ARROW => '#FF5722',
            self::TYPE_RECTANGLE => '#607D8B',
            self::TYPE_CIRCLE => '#795548'
        ];
    }

    /**
     * Resolve the annotation
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
     * Unresolve the annotation
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
     * Get annotation data for PDF.js
     */
    public function toPdfJsAnnotation(): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->annotation_type,
            'pageNumber' => $this->page_number,
            'color' => $this->color,
            'opacity' => (float) $this->opacity,
            'coordinates' => $this->coordinates,
            'content' => $this->content,
            'style' => $this->style ?? [],
            'metadata' => $this->metadata ?? [],
            'createdAt' => $this->created_at->toISOString(),
            'createdBy' => $this->user->name,
            'isResolved' => $this->is_resolved,
            'commentCount' => $this->comments()->count()
        ];

        return $data;
    }
}
