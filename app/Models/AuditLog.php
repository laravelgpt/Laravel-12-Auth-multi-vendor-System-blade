<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_type',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'severity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Event types
     */
    const EVENT_LOGIN = 'login';
    const EVENT_LOGOUT = 'logout';
    const EVENT_PASSWORD_CHANGE = 'password_change';
    const EVENT_ROLE_ASSIGNED = 'role_assigned';
    const EVENT_ROLE_REMOVED = 'role_removed';
    const EVENT_PERMISSION_ASSIGNED = 'permission_assigned';
    const EVENT_PERMISSION_REMOVED = 'permission_removed';
    const EVENT_ACCOUNT_LOCKED = 'account_locked';
    const EVENT_ACCOUNT_UNLOCKED = 'account_unlocked';
    const EVENT_PROFILE_UPDATED = 'profile_updated';
    const EVENT_ACCOUNT_DELETED = 'account_deleted';
    const EVENT_API_TOKEN_CREATED = 'api_token_created';
    const EVENT_API_TOKEN_REVOKED = 'api_token_revoked';
    const EVENT_SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    const EVENT_SECURITY_VIOLATION = 'security_violation';

    /**
     * Actions
     */
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';

    /**
     * Severity levels
     */
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Get the user that owns the audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get logs by event type
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get logs by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to get logs by resource
     */
    public function scopeByResource($query, $resourceType, $resourceId = null)
    {
        $query->where('resource_type', $resourceType);
        
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        
        return $query;
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get logs by IP
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope to get critical logs
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    /**
     * Scope to get error logs
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('severity', [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL]);
    }

    /**
     * Check if log is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Check if log is an error
     */
    public function isError(): bool
    {
        return in_array($this->severity, [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL]);
    }

    /**
     * Check if log is a warning
     */
    public function isWarning(): bool
    {
        return $this->severity === self::SEVERITY_WARNING;
    }

    /**
     * Get formatted description
     */
    public function getFormattedDescriptionAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->description));
    }

    /**
     * Get resource name
     */
    public function getResourceNameAttribute(): ?string
    {
        if (!$this->resource_type || !$this->resource_id) {
            return null;
        }

        $modelClass = "App\\Models\\{$this->resource_type}";
        
        if (class_exists($modelClass)) {
            $model = $modelClass::find($this->resource_id);
            return $model ? $model->name ?? $model->id : null;
        }

        return null;
    }
}
