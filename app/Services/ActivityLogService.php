<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ActivityLogService
{
    public function logActivity(User $user, string $action, array $data = []): void
    {
        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('activity')->info('User Activity', $logData);
    }

    public function logSecurityEvent(User $user, string $event, array $data = []): void
    {
        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'event' => $event,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->warning('Security Event', $logData);
    }

    public function logFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        $logData = [
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->warning('Failed Login Attempt', $logData);
    }
}
