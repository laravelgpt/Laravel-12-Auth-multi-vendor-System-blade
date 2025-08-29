<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log user login attempt
     */
    public function logLoginAttempt(
        string $email,
        string $ipAddress,
        string $userAgent,
        string $loginMethod,
        bool $isSuccessful,
        ?string $failureReason = null,
        ?User $user = null
    ): void {
        // Log to LoginLog table
        LoginLog::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'login_method' => $loginMethod,
            'is_successful' => $isSuccessful,
            'failure_reason' => $failureReason,
            'attempt_count' => $this->getAttemptCount($email, $ipAddress),
            'is_suspicious' => $this->isSuspiciousActivity($email, $ipAddress),
            'metadata' => [
                'browser' => $this->getBrowserInfo($userAgent),
                'platform' => $this->getPlatformInfo($userAgent),
                'device_type' => $this->getDeviceType($userAgent),
            ],
        ]);

        // If successful and user exists, log to LoginHistory
        if ($isSuccessful && $user) {
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'login_method' => $loginMethod,
                'is_successful' => true,
                'location' => $this->getLocationInfo($ipAddress),
                'device_type' => $this->getDeviceType($userAgent),
                'browser' => $this->getBrowserInfo($userAgent),
                'platform' => $this->getPlatformInfo($userAgent),
                'metadata' => [
                    'session_id' => session()->getId(),
                    'login_time' => now()->toISOString(),
                ],
            ]);
        }

        // Log to Laravel's log system
        $level = $isSuccessful ? 'info' : 'warning';
        Log::channel('security')->$level('Login attempt', [
            'email' => $email,
            'ip_address' => $ipAddress,
            'login_method' => $loginMethod,
            'successful' => $isSuccessful,
            'failure_reason' => $failureReason,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Log user logout
     */
    public function logLogout(User $user, Request $request): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_LOGOUT,
            'action' => AuditLog::ACTION_LOGOUT,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'User logged out successfully',
            'severity' => AuditLog::SEVERITY_INFO,
            'metadata' => [
                'session_id' => session()->getId(),
                'logout_time' => now()->toISOString(),
            ],
        ]);

        Log::channel('security')->info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Log password change
     */
    public function logPasswordChange(User $user, Request $request): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_PASSWORD_CHANGE,
            'action' => AuditLog::ACTION_UPDATE,
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'Password changed successfully',
            'severity' => AuditLog::SEVERITY_WARNING,
            'metadata' => [
                'changed_at' => now()->toISOString(),
            ],
        ]);

        Log::channel('security')->warning('Password changed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Log role assignment
     */
    public function logRoleAssignment(User $user, string $role, Request $request): void
    {
        $assignedBy = auth()->id() ?? null;
        
        AuditLog::create([
            'user_id' => $assignedBy,
            'event_type' => AuditLog::EVENT_ROLE_ASSIGNED,
            'action' => AuditLog::ACTION_UPDATE,
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Role '{$role}' assigned to user",
            'new_values' => ['role' => $role],
            'severity' => AuditLog::SEVERITY_INFO,
            'metadata' => [
                'assigned_by' => $assignedBy,
                'assigned_at' => now()->toISOString(),
            ],
        ]);

        Log::channel('security')->info('Role assigned', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'assigned_by' => $assignedBy,
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(
        string $event,
        array $data,
        string $severity = AuditLog::SEVERITY_WARNING
    ): void {
        $userId = auth()->check() ? auth()->id() : null;
        
        AuditLog::create([
            'user_id' => $userId,
            'event_type' => AuditLog::EVENT_SUSPICIOUS_ACTIVITY,
            'action' => AuditLog::ACTION_VIEW,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $event,
            'severity' => $severity,
            'metadata' => $data,
        ]);

        Log::channel('security')->warning('Suspicious activity detected', [
            'event' => $event,
            'data' => $data,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Log security violation
     */
    public function logSecurityViolation(
        string $violation,
        array $data,
        string $severity = AuditLog::SEVERITY_ERROR
    ): void {
        $userId = auth()->check() ? auth()->id() : null;
        
        AuditLog::create([
            'user_id' => $userId,
            'event_type' => AuditLog::EVENT_SECURITY_VIOLATION,
            'action' => AuditLog::ACTION_VIEW,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $violation,
            'severity' => $severity,
            'metadata' => $data,
        ]);

        Log::channel('security')->error('Security violation detected', [
            'violation' => $violation,
            'data' => $data,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get attempt count for email/IP combination
     */
    private function getAttemptCount(string $email, string $ipAddress): int
    {
        return LoginLog::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->count() + 1;
    }

    /**
     * Check for suspicious activity
     */
    private function isSuspiciousActivity(string $email, string $ipAddress): bool
    {
        $recentAttempts = LoginLog::where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        return $recentAttempts > 10;
    }

    /**
     * Get browser information from user agent
     */
    private function getBrowserInfo(string $userAgent): string
    {
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
     * Get platform information from user agent
     */
    private function getPlatformInfo(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iOS')) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get device type from user agent
     */
    private function getDeviceType(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            return 'mobile';
        } elseif (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get location information from IP (placeholder)
     */
    private function getLocationInfo(string $ipAddress): ?string
    {
        // This would typically use a geolocation service
        // For now, return null
        return null;
    }
}
