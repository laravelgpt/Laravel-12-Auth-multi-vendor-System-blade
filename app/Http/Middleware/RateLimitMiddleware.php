<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $key = $this->generateKey($request, $type);
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);

        // Check if rate limit is exceeded
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logSuspiciousActivity($request, $type);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        // Hit the rate limiter
        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
        ]);

        return $response;
    }

    /**
     * Generate a unique key for the rate limiter
     */
    private function generateKey(Request $request, string $type): string
    {
        $ip = $request->ip();
        $userAgent = md5($request->userAgent());
        
        // For authenticated users, also include user ID
        if ($request->user()) {
            return "rate_limit:{$type}:{$ip}:{$userAgent}:user_{$request->user()->id}";
        }

        return "rate_limit:{$type}:{$ip}:{$userAgent}";
    }

    /**
     * Get maximum attempts based on type
     */
    private function getMaxAttempts(string $type): int
    {
        return match ($type) {
            'login' => 5,      // 5 login attempts
            'register' => 3,   // 3 registration attempts
            'otp' => 3,        // 3 OTP attempts
            'api' => 60,       // 60 API requests
            'password-reset' => 2, // 2 password reset attempts
            'social' => 5,     // 5 social login attempts
            default => 30,     // 30 default requests
        };
    }

    /**
     * Get decay time in minutes based on type
     */
    private function getDecayMinutes(string $type): int
    {
        return match ($type) {
            'login' => 15,     // 15 minutes for login
            'register' => 60,  // 1 hour for registration
            'otp' => 5,        // 5 minutes for OTP
            'api' => 1,        // 1 minute for API
            'password-reset' => 60, // 1 hour for password reset
            'social' => 15,    // 15 minutes for social login
            default => 5,      // 5 minutes default
        };
    }

    /**
     * Log suspicious activity for security monitoring
     */
    private function logSuspiciousActivity(Request $request, string $type): void
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'type' => $type,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'timestamp' => now(),
        ];

        Log::warning('Rate limit exceeded', $data);

        // Store in cache for security dashboard
        $cacheKey = "security_alerts:" . now()->format('Y-m-d');
        $alerts = Cache::get($cacheKey, []);
        $alerts[] = $data;
        Cache::put($cacheKey, $alerts, now()->addDays(7));

        // Block IP temporarily if too many different endpoints are hit
        $this->checkForDDOSPattern($request);
    }

    /**
     * Check for DDOS patterns and temporarily block IPs
     */
    private function checkForDDOSPattern(Request $request): void
    {
        $ip = $request->ip();
        $ddosKey = "ddos_check:{$ip}";
        
        $violations = Cache::get($ddosKey, 0);
        $violations++;
        
        Cache::put($ddosKey, $violations, now()->addMinutes(30));

        // If more than 5 different rate limit violations in 30 minutes
        if ($violations > 5) {
            $blockKey = "blocked_ip:{$ip}";
            Cache::put($blockKey, true, now()->addHours(1));
            
            Log::critical('IP temporarily blocked for DDOS pattern', [
                'ip' => $ip,
                'violations' => $violations,
                'blocked_until' => now()->addHours(1),
            ]);
        }
    }

    /**
     * Check if IP is temporarily blocked
     */
    public static function isBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}");
    }
}
