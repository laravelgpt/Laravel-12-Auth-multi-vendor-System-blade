<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Public Authentication Routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    
    // Social Login Routes
    Route::get('social/{provider}/redirect', [AuthController::class, 'socialRedirect']);
    Route::get('social/{provider}/callback', [AuthController::class, 'socialCallback']);
    
    // Password Reset Routes
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    
    // Password Validation Routes (Public)
    Route::post('validate-password', [AuthController::class, 'validatePassword']);
    Route::post('password-breach-details', [AuthController::class, 'getPasswordBreachDetails']);
    
    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // General Auth Routes
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        
        // Admin Routes
        Route::middleware('role:Admin')->prefix('admin')->group(function () {
            Route::get('dashboard', [AdminController::class, 'dashboard']);
            Route::get('users', [AdminController::class, 'users']);
            Route::get('users/{user}', [AdminController::class, 'showUser']);
            Route::put('users/{user}', [AdminController::class, 'updateUser']);
            Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
            Route::post('users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus']);
            Route::get('analytics', [AdminController::class, 'analytics']);
            Route::get('audit-logs', [AdminController::class, 'auditLogs']);
            Route::get('login-logs', [AdminController::class, 'loginLogs']);
            Route::get('system-stats', [AdminController::class, 'systemStats']);
            Route::post('users/{user}/assign-role', [AdminController::class, 'assignRole']);
            Route::post('users/{user}/remove-role', [AdminController::class, 'removeRole']);
        });
        
        // Customer Routes
        Route::middleware('role:Customer')->prefix('customer')->group(function () {
            Route::get('profile', [CustomerController::class, 'profile']);
            Route::put('profile', [CustomerController::class, 'updateProfile']);
            Route::post('change-password', [CustomerController::class, 'changePassword']);
            Route::get('dashboard', [CustomerController::class, 'dashboard']);
            Route::get('activity-logs', [CustomerController::class, 'activityLogs']);
            Route::delete('account', [CustomerController::class, 'deleteAccount']);
            Route::get('preferences', [CustomerController::class, 'preferences']);
            Route::put('preferences', [CustomerController::class, 'updatePreferences']);
        });
        
        // Shared Routes (Both Admin and Customer)
        Route::middleware('role:Admin|Customer')->group(function () {
            Route::get('notifications', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifications retrieved successfully',
                    'data' => []
                ]);
            });
            
            Route::post('mark-notifications-read', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifications marked as read'
                ]);
            });
        });
    });
    
    // Rate Limited Routes
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::post('contact', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:1000'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully'
            ]);
        });
    });
});

// Health Check Route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'laravel' => app()->version()
    ]);
});

// API Documentation Route
Route::get('docs', function () {
    return response()->json([
        'api_name' => config('app.name') . ' API',
        'version' => 'v1',
        'documentation' => 'https://your-domain.com/api/docs',
        'endpoints' => [
            'authentication' => '/api/v1/login',
            'admin' => '/api/v1/admin/*',
            'customer' => '/api/v1/customer/*',
            'health' => '/api/health'
        ]
    ]);
});
