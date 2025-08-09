<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'content',
        'variables',
        'is_active',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if template is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get template variables as array
     */
    public function getVariablesArray(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Get template category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Get template content preview
     */
    public function getContentPreviewAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 200);
    }

    /**
     * Generate document from template with variables
     */
    public function generateDocument(array $variables = []): string
    {
        $content = $this->content;

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        // Add default variables
        $defaultVariables = [
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'user_name' => Auth::user()->name,
            'user_email' => Auth::user()->email,
            'template_name' => $this->name,
        ];

        foreach ($defaultVariables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        return $content;
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        $variables = $this->getVariablesArray();
        
        // Add default variables
        $defaultVariables = [
            'date' => 'Current date (Y-m-d)',
            'time' => 'Current time (H:i:s)',
            'user_name' => 'Current user name',
            'user_email' => 'Current user email',
            'template_name' => 'Template name',
        ];

        return array_merge($defaultVariables, $variables);
    }

    /**
     * Validate variables against template
     */
    public function validateVariables(array $variables): array
    {
        $errors = [];
        $requiredVariables = $this->getVariablesArray();

        foreach ($requiredVariables as $variable => $description) {
            if (!isset($variables[$variable]) || empty($variables[$variable])) {
                $errors[$variable] = "Variable '{$variable}' is required";
            }
        }

        return $errors;
    }
}
