<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityService
{
    /**
     * Check if user account should be locked due to failed attempts
     */
    public function checkAccountLockout(string $email): bool
    {
        $key = "failed_attempts_{$email}";
        $failedAttempts = Cache::get($key, 0);
        
        return $failedAttempts >= 5; // Lock after 5 failed attempts
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedAttempt(string $email): void
    {
        $key = "failed_attempts_{$email}";
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, now()->addMinutes(30));
        
        Log::warning('Failed login attempt', [
            'email' => $email,
            'attempts' => $attempts + 1,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Clear failed attempts for successful login
     */
    public function clearFailedAttempts(string $email): void
    {
        $key = "failed_attempts_{$email}";
        Cache::forget($key);
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIpBlacklisted(string $ip): bool
    {
        return Cache::has("blacklisted_ip_{$ip}");
    }

    /**
     * Blacklist IP address
     */
    public function blacklistIp(string $ip, int $minutes = 60): void
    {
        Cache::put("blacklisted_ip_{$ip}", true, now()->addMinutes($minutes));
        
        Log::warning('IP address blacklisted', [
            'ip' => $ip,
            'duration_minutes' => $minutes,
        ]);
    }

    /**
     * Check for suspicious activity
     */
    public function detectSuspiciousActivity(string $email, string $ip): bool
    {
        $key = "login_attempts_{$ip}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts > 10) { // More than 10 attempts from same IP
            $this->blacklistIp($ip, 30);
            return true;
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes(5));
        return false;
    }

    /**
     * Generate security audit log
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        Log::channel('security')->info($event, array_merge($data, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]));
    }
}
