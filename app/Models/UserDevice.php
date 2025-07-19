<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'browser',
        'device',
        'platform',
        'ip_address',
        'location',
        'last_active_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 