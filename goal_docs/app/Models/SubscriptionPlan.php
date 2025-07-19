<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',  
        'description',
        'price',
        'billing_cycle',
        'storage_limit',
        'user_limit',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'storage_limit' => 'integer',
        'user_limit' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenantSubscriptions()
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedStorageLimitAttribute(): string
    {
        $gb = $this->storage_limit / 1073741824; // Convert bytes to GB
        return number_format($gb, 1) . ' GB';
    }
}