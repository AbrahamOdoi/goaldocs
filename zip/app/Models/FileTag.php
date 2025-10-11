<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'tag_name',
        'color',
        'user_type',
        'type_name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the file this tag belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created this tag
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for filtering by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for filtering by specific organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)->where('type_name', $typeName);
    }

    /**
     * Scope for filtering by tag name
     */
    public function scopeByTagName($query, $tagName)
    {
        return $query->where('tag_name', 'like', '%' . $tagName . '%');
    }

    /**
     * Get popular tags for an organization
     */
    public static function getPopularTags($userType, $typeName, $limit = 10)
    {
        return static::forOrganization($userType, $typeName)
            ->selectRaw('tag_name, color, COUNT(*) as usage_count')
            ->groupBy('tag_name', 'color')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get tag suggestions for autocomplete
     */
    public static function getTagSuggestions($userType, $typeName, $query, $limit = 10)
    {
        return static::forOrganization($userType, $typeName)
            ->selectRaw('DISTINCT tag_name, color')
            ->where('tag_name', 'like', $query . '%')
            ->limit($limit)
            ->get();
    }
}
