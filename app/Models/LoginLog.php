<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'login_method',
        'is_successful',
        'failure_reason',
        'attempt_count',
        'is_suspicious',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_successful' => 'boolean',
        'is_suspicious' => 'boolean',
        'attempt_count' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Login methods
     */
    const METHOD_PASSWORD = 'password';
    const METHOD_OTP = 'otp';
    const METHOD_SOCIAL = 'social';
    const METHOD_API = 'api';

    /**
     * Get the user that owns the login log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Scope to get successful logins
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope to get failed logins
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Scope to get suspicious logins
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope to get logins by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('login_method', $method);
    }

    /**
     * Scope to get recent logins
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get logins by IP
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Get device name from user agent
     */
    public function getDeviceNameAttribute(): string
    {
        $userAgent = $this->user_agent;
        
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserNameAttribute(): string
    {
        $userAgent = $this->user_agent;
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Check if login was successful
     */
    public function isSuccessful(): bool
    {
        return $this->is_successful;
    }

    /**
     * Check if login failed
     */
    public function isFailed(): bool
    {
        return !$this->is_successful;
    }

    /**
     * Check if login was suspicious
     */
    public function isSuspicious(): bool
    {
        return $this->is_suspicious;
    }

    /**
     * Get failure reason
     */
    public function getFailureReason(): ?string
    {
        return $this->failure_reason;
    }

    /**
     * Get attempt count
     */
    public function getAttemptCount(): int
    {
        return $this->attempt_count;
    }
}
