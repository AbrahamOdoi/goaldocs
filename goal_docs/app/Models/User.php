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
        'type',
        'type_name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Remove all tenant-related relationships and helpers

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }
}
