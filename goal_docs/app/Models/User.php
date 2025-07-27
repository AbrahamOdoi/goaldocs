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

    /**
     * Get file permissions assigned to this user
     */
    public function filePermissions()
    {
        return $this->morphMany(FilePermission::class, 'assignable');
    }
}
