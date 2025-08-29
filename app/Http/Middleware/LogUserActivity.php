<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log user activity for security monitoring
        if (auth()->check()) {
            $this->logActivity($request, $response);
        }

        // Log suspicious activity
        $this->logSuspiciousActivity($request, $response);

        return $response;
    }

    /**
     * Log authenticated user activity
     */
    private function logActivity(Request $request, Response $response): void
    {
        $user = auth()->user();
        
        // Only log important actions, not every page view
        $shouldLog = $this->shouldLogRequest($request);
        
        if ($shouldLog) {
            Log::channel('audit')->info('User Activity', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(Request $request, Response $response): void
    {
        $suspicious = false;
        $reason = '';

        // Check for suspicious patterns
        if ($response->getStatusCode() === 401) {
            $suspicious = true;
            $reason = 'Unauthorized access attempt';
        } elseif ($response->getStatusCode() === 403) {
            $suspicious = true;
            $reason = 'Forbidden access attempt';
        } elseif ($response->getStatusCode() === 429) {
            $suspicious = true;
            $reason = 'Rate limit exceeded';
        } elseif ($this->hasSuspiciousParams($request)) {
            $suspicious = true;
            $reason = 'Suspicious request parameters';
        }

        if ($suspicious) {
            Log::channel('security')->warning('Suspicious Activity', [
                'reason' => $reason,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->header('User-Agent'),
                'user_id' => auth()->id(),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Determine if the request should be logged
     */
    private function shouldLogRequest(Request $request): bool
    {
        $loggedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $loggedPaths = ['/login', '/logout', '/register', '/password', '/profile'];
        
        return in_array($request->method(), $loggedMethods) ||
               collect($loggedPaths)->some(fn($path) => str_contains($request->path(), $path));
    }

    /**
     * Check for suspicious request parameters
     */
    private function hasSuspiciousParams(Request $request): bool
    {
        $suspiciousPatterns = [
            'script', 'javascript:', 'vbscript:', 'onload=', 'onerror=',
            'union select', 'drop table', 'delete from', 'insert into',
            '../', '..\\', 'etc/passwd', 'cmd.exe', 'powershell',
        ];

        $input = strtolower(json_encode($request->all()));
        
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($input, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }
}
