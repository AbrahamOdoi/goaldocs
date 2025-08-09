<?php
// app/Models/User.php (Central Database)

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
//class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'type',
        'type_name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'is_admin',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
    ];

    // Remove all tenant-related relationships and helpers

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }



    public function positions()
    {
        return $this->belongsToMany(Position::class, 'user_positions')
                    ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active'])
                    ->withTimestamps();
    }

    public function primaryPosition()
    {
        return $this->positions()->wherePivot('is_primary', true)->wherePivot('is_active', true)->first();
    }

    public function activePositions()
    {
        return $this->positions()->wherePivot('is_active', true);
    }

    public function departments()
    {
        return $this->hasManyThrough(Department::class, Position::class, 'department_id', 'id', 'id', 'department_id');
    }

    // Accessor methods for first_name and last_name
    public function getFirstNameAttribute()
    {
        $nameParts = explode(' ', $this->name);
        return $nameParts[0] ?? '';
    }

    public function getLastNameAttribute()
    {
        $nameParts = explode(' ', $this->name);
        if (count($nameParts) > 1) {
            array_shift($nameParts); // Remove first name
            return implode(' ', $nameParts); // Join remaining parts as last name
        }
        return '';
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get file permissions assigned to this user
     */
    public function filePermissions()
    {
        return $this->hasMany(FilePermission::class);
    }

    /**
     * Get files uploaded by this user
     */
    public function files()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    /**
     * Get activities performed by this user
     */
    public function activities()
    {
        return $this->hasMany(RecentActivity::class);
    }

    /**
     * Get comments made by this user
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get document locks created by this user
     */
    public function documentLocks()
    {
        return $this->hasMany(DocumentLock::class);
    }

    /**
     * Get workflow instances initiated by this user
     */
    public function workflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'initiated_by');
    }

    /**
     * Get security audits for this user
     */
    public function securityAudits()
    {
        return $this->hasMany(SecurityAudit::class);
    }
}
