<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function admin(Request $request): View
    {
        $user = $request->user();
        
        // Get user statistics
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::role('Customer')->count(),
            'total_admins' => User::role('Admin')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_users_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        // Get recent activity
        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // Get login statistics
        $loginStats = $this->getLoginStatistics();

        return view('dashboard.admin', [
            'user' => $user,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'loginStats' => $loginStats,
        ]);
    }

    /**
     * Show customer dashboard
     */
    public function customer(Request $request): View
    {
        $user = $request->user();
        
        $stats = [
            'last_login' => $user->last_login_at,
            'account_created' => $user->created_at,
            'is_verified' => $user->email_verified_at !== null,
            'total_logins' => $this->getUserLoginCount($user),
            'account_age' => $user->created_at->diffForHumans(),
            'last_activity' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
        ];

        // Get user's recent activity
        $recentActivity = $this->getUserRecentActivity($user);

        return view('dashboard.customer', [
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Get login statistics for admin dashboard
     */
    private function getLoginStatistics(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'logins_today' => User::whereDate('last_login_at', $today)->count(),
            'logins_week' => User::whereBetween('last_login_at', [$weekStart, now()])->count(),
            'logins_month' => User::whereBetween('last_login_at', [$monthStart, now()])->count(),
            'active_users_percentage' => round((User::where('is_active', true)->count() / User::count()) * 100, 1),
        ];
    }

    /**
     * Get user login count
     */
    private function getUserLoginCount(User $user): int
    {
        // This would be implemented with a login history table
        // For now, we'll return a placeholder
        return rand(1, 50); // Placeholder
    }

    /**
     * Get user's recent activity
     */
    private function getUserRecentActivity(User $user): array
    {
        // This would be implemented with an activity log table
        // For now, we'll return placeholder data
        return [
            [
                'type' => 'login',
                'description' => 'Logged in successfully',
                'timestamp' => $user->last_login_at ?? now()->subHours(2),
                'ip' => request()->ip(),
            ],
            [
                'type' => 'profile_update',
                'description' => 'Updated profile information',
                'timestamp' => now()->subDays(1),
                'ip' => request()->ip(),
            ],
        ];
    }
}
