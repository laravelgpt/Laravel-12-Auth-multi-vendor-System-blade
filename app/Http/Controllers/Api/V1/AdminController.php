<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::role('Customer')->count(),
            'total_admins' => User::role('Admin')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get all users
     */
    public function users(): JsonResponse
    {
        $users = User::with('roles')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Get specific user
     */
    public function showUser(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($request->only(['name', 'email', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => [
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Get analytics data
     */
    public function analytics(): JsonResponse
    {
        $analytics = [
            'user_growth' => $this->getUserGrowthData(),
            'login_activity' => $this->getLoginActivityData(),
            'role_distribution' => $this->getRoleDistributionData(),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get audit logs
     */
    public function auditLogs(): JsonResponse
    {
        // This would be implemented with an audit log system
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Audit logs feature coming soon',
        ]);
    }

    /**
     * Get login logs
     */
    public function loginLogs(): JsonResponse
    {
        // This would be implemented with a login history system
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Login logs feature coming soon',
        ]);
    }

    /**
     * Get system statistics
     */
    public function systemStats(): JsonResponse
    {
        $stats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connections' => config('database.connections'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $role = Role::whereName($request->role)->first();
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' assigned successfully",
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $role = Role::whereName($request->role)->first();
        $user->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' removed successfully",
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'users' => User::whereDate('created_at', $date)->count(),
            ];
        }
        return $data;
    }

    /**
     * Get login activity data
     */
    private function getLoginActivityData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'logins' => User::whereDate('last_login_at', $date)->count(),
            ];
        }
        return $data;
    }

    /**
     * Get role distribution data
     */
    private function getRoleDistributionData(): array
    {
        $roles = Role::all();
        $data = [];

        foreach ($roles as $role) {
            $data[] = [
                'role' => $role->name,
                'count' => User::role($role->name)->count(),
            ];
        }

        return $data;
    }
}
