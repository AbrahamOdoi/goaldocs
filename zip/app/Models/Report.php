<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'config',
        'schedule',
        'is_public',
        'is_active',
        'last_generated_at',
        'next_generation_at',
    ];

    protected $casts = [
        'config' => 'array',
        'schedule' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
        'next_generation_at' => 'datetime',
    ];

    // Report types
    const TYPE_DOCUMENT_USAGE = 'document_usage';
    const TYPE_PROCESSING = 'processing';
    const TYPE_STORAGE = 'storage';
    const TYPE_USER_ACTIVITY = 'user_activity';
    const TYPE_CUSTOM = 'custom';

    // Schedule types
    const SCHEDULE_DAILY = 'daily';
    const SCHEDULE_WEEKLY = 'weekly';
    const SCHEDULE_MONTHLY = 'monthly';
    const SCHEDULE_QUARTERLY = 'quarterly';
    const SCHEDULE_YEARLY = 'yearly';

    /**
     * Get the user that owns the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the report generations.
     */
    public function generations()
    {
        return $this->hasMany(ReportGeneration::class);
    }

    /**
     * Get the latest generation.
     */
    public function latestGeneration()
    {
        return $this->hasOne(ReportGeneration::class)->latest();
    }

    /**
     * Scope for active reports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public reports.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for scheduled reports.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('schedule');
    }

    /**
     * Get report type options.
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_DOCUMENT_USAGE => 'Document Usage',
            self::TYPE_PROCESSING => 'Processing Analytics',
            self::TYPE_STORAGE => 'Storage Analytics',
            self::TYPE_USER_ACTIVITY => 'User Activity',
            self::TYPE_CUSTOM => 'Custom Report',
        ];
    }

    /**
     * Get schedule options.
     */
    public static function getScheduleOptions()
    {
        return [
            self::SCHEDULE_DAILY => 'Daily',
            self::SCHEDULE_WEEKLY => 'Weekly',
            self::SCHEDULE_MONTHLY => 'Monthly',
            self::SCHEDULE_QUARTERLY => 'Quarterly',
            self::SCHEDULE_YEARLY => 'Yearly',
        ];
    }

    /**
     * Check if report is scheduled.
     */
    public function isScheduled()
    {
        return !empty($this->schedule);
    }

    /**
     * Get next generation date.
     */
    public function getNextGenerationDate()
    {
        if (!$this->isScheduled()) {
            return null;
        }

        $lastGenerated = $this->last_generated_at ?? now()->subDay();
        $schedule = $this->schedule;

        switch ($schedule['type']) {
            case self::SCHEDULE_DAILY:
                return $lastGenerated->addDay();
            case self::SCHEDULE_WEEKLY:
                return $lastGenerated->addWeek();
            case self::SCHEDULE_MONTHLY:
                return $lastGenerated->addMonth();
            case self::SCHEDULE_QUARTERLY:
                return $lastGenerated->addMonths(3);
            case self::SCHEDULE_YEARLY:
                return $lastGenerated->addYear();
            default:
                return null;
        }
    }

    /**
     * Check if report should be generated.
     */
    public function shouldGenerate()
    {
        if (!$this->isScheduled() || !$this->is_active) {
            return false;
        }

        return $this->next_generation_at && $this->next_generation_at->isPast();
    }

    /**
     * Get report configuration.
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config ?? [];
        }

        return data_get($this->config, $key, $default);
    }

    /**
     * Set report configuration.
     */
    public function setConfig($key, $value)
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        return $this;
    }
}
