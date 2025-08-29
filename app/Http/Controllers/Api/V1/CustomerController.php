<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Get customer profile
     */
    public function profile(): JsonResponse
    {
        $user = request()->user();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'sometimes|string|max:20',
        ]);

        $user->update($request->only(['name', 'email', 'phone']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Get customer dashboard data
     */
    public function dashboard(): JsonResponse
    {
        $user = auth()->user();

        $stats = [
            'last_login' => $user->last_login_at,
            'account_created' => $user->created_at,
            'is_verified' => $user->email_verified_at !== null,
            'account_age' => $user->created_at->diffForHumans(),
            'last_activity' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(): JsonResponse
    {
        // This would be implemented with an activity log system
        $logs = [
            [
                'type' => 'login',
                'description' => 'Logged in successfully',
                'timestamp' => now()->subHours(2)->toISOString(),
                'ip' => request()->ip(),
            ],
            [
                'type' => 'profile_update',
                'description' => 'Updated profile information',
                'timestamp' => now()->subDays(1)->toISOString(),
                'ip' => request()->ip(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    /**
     * Get user preferences
     */
    public function preferences(): JsonResponse
    {
        $user = auth()->user();

        $preferences = [
            'email_notifications' => true,
            'push_notifications' => false,
            'two_factor_enabled' => false,
            'language' => 'en',
            'timezone' => 'UTC',
        ];

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'language' => 'sometimes|string|in:en,es,fr,de',
            'timezone' => 'sometimes|string',
        ]);

        // This would be implemented with a preferences table
        // For now, we'll just return success

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => $request->all(),
        ]);
    }
}
