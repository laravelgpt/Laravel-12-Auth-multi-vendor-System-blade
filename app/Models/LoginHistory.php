<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_method',
        'social_provider',
        'is_successful',
        'failure_reason',
        'location',
        'device_type',
        'browser',
        'platform',
        'metadata',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful logins
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope for failed logins
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Scope for social logins
     */
    public function scopeSocial($query)
    {
        return $query->whereNotNull('social_provider');
    }

    /**
     * Get device information from user agent
     */
    public function getDeviceInfoAttribute()
    {
        if (!$this->user_agent) {
            return null;
        }

        $userAgent = $this->user_agent;
        
        // Simple device detection
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
}
