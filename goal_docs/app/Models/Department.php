<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'user_type',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function activePositions()
    {
        return $this->positions()->where('is_active', true);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Position::class);
    }

    public function getDisplayNameAttribute()
    {
        $typeLabels = [
            'organisation' => 'Department',
            'family' => 'Role',
            'government' => 'Agency',
            'social_group' => 'Group',
            'professional_group' => 'Division',
            'educational_institution' => 'Department',
            'non_profit' => 'Department',
            'individual' => 'Category',
        ];

        return $typeLabels[$this->user_type] ?? 'Department';
    }

    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 