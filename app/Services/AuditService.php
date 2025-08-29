<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Log a login attempt
     */
    public function logLoginAttempt(
        ?User $user,
        string $email,
        string $loginType,
        string $status,
        ?string $failureReason = null,
        ?Request $request = null
    ): LoginLog {
        $request = $request ?: request();
        
        return LoginLog::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_type' => $loginType,
            'status' => $status,
            'failure_reason' => $failureReason,
            'location' => $this->getLocationData($request->ip()),
            'device_info' => $this->getDeviceInfo($request),
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Log a successful login
     */
    public function logSuccessfulLogin(User $user, string $loginType, ?Request $request = null): LoginLog
    {
        $loginLog = $this->logLoginAttempt(
            $user,
            $user->email,
            $loginType,
            LoginLog::STATUS_SUCCESS,
            null,
            $request
        );

        // Update user's last login
        $user->updateLastLogin();

        // Log for monitoring
        Log::info('Successful login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'login_type' => $loginType,
            'ip_address' => $request?->ip(),
        ]);

        return $loginLog;
    }

    /**
     * Log a failed login
     */
    public function logFailedLogin(
        string $email,
        string $loginType,
        string $reason,
        ?Request $request = null
    ): LoginLog {
        $loginLog = $this->logLoginAttempt(
            null,
            $email,
            $loginType,
            LoginLog::STATUS_FAILED,
            $reason,
            $request
        );

        // Log for security monitoring
        Log::warning('Failed login attempt', [
            'email' => $email,
            'login_type' => $loginType,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
        ]);

        // Check for suspicious activity
        $this->checkSuspiciousActivity($email, $request?->ip());

        return $loginLog;
    }

    /**
     * Log a logout event
     */
    public function logLogout(User $user): void
    {
        // Update the most recent successful login log
        $recentLogin = LoginLog::where('user_id', $user->id)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($recentLogin) {
            $recentLogin->update(['logout_at' => now()]);
        }

        Log::info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Get location data from IP address
     */
    private function getLocationData(string $ip): ?array
    {
        // In production, you would use a service like MaxMind GeoIP2
        // For now, return basic info
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return [
                'country' => 'Local',
                'city' => 'Localhost',
                'timezone' => config('app.timezone'),
            ];
        }

        // Placeholder for actual geolocation service
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'timezone' => config('app.timezone'),
        ];
    }

    /**
     * Get device information from request
     */
    private function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'platform' => $this->getPlatform($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'is_mobile' => $request->header('X-Mobile-Device') !== null || 
                          str_contains($userAgent, 'Mobile'),
        ];
    }

    /**
     * Extract platform from user agent
     */
    private function getPlatform(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        
        return 'Unknown';
    }

    /**
     * Extract browser from user agent
     */
    private function getBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        
        return 'Unknown';
    }

    /**
     * Check for suspicious login activity
     */
    private function checkSuspiciousActivity(string $email, ?string $ip): void
    {
        // Check for multiple failed attempts from same IP
        $recentFailures = LoginLog::where('ip_address', $ip)
            ->where('status', LoginLog::STATUS_FAILED)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFailures >= 5) {
            Log::critical('Multiple failed login attempts detected', [
                'email' => $email,
                'ip_address' => $ip,
                'failed_attempts' => $recentFailures,
            ]);

            // In production, you might want to temporarily block this IP
            // or trigger additional security measures
        }

        // Check for failed attempts on this email
        $emailFailures = LoginLog::where('email', $email)
            ->where('status', LoginLog::STATUS_FAILED)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($emailFailures >= 3) {
            Log::warning('Multiple failed login attempts for email', [
                'email' => $email,
                'failed_attempts' => $emailFailures,
            ]);
        }
    }

    /**
     * Get login statistics for a user
     */
    public function getUserLoginStats(User $user): array
    {
        $totalLogins = LoginLog::where('user_id', $user->id)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->count();

        $recentLogins = LoginLog::where('user_id', $user->id)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $failedAttempts = LoginLog::where('user_id', $user->id)
            ->where('status', LoginLog::STATUS_FAILED)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $lastLogin = LoginLog::where('user_id', $user->id)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->latest()
            ->first();

        return [
            'total_logins' => $totalLogins,
            'recent_logins' => $recentLogins,
            'failed_attempts' => $failedAttempts,
            'last_login' => $lastLogin,
        ];
    }
}
