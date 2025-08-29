<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\PasswordBreachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Registration validation failed', [
                'errors' => $e->errors(),
                'data' => $request->except(['password'])
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password'])
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login with email and password
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->login($request->validated());
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Send OTP for email verification
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $this->authService->sendOtp($request->email);

            return response()->json([
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('OTP send failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify OTP and login
     */
    public function verifyOtp(OtpRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->verifyOtp($request->email, $request->otp);
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'message' => 'OTP verified successfully',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Social login redirect
     */
    public function socialRedirect(string $provider): JsonResponse
    {
        try {
            $redirectUrl = \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect()->getTargetUrl();

            return response()->json([
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Social redirect failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);

            return response()->json([
                'message' => 'Social login not available',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Social login callback
     */
    public function socialCallback(string $provider): JsonResponse
    {
        try {
            $socialUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->user();

            $socialData = [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ];

            $user = $this->authService->handleSocialLogin($socialData, $provider);
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'message' => 'Social login successful',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('Social callback failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);

            return response()->json([
                'message' => 'Social login failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user())
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->revokeAllTokens($request->user());

            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->authService->revokeAllTokens($user);
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate password in real-time
     */
    public function validatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:1'
        ]);

        try {
            $validation = $this->authService->validatePasswordChange($request->password);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'password' => $request->password,
                    'is_safe' => $validation['is_safe'],
                    'strength' => $validation['strength'],
                    'breach_status' => $validation['breach_status'],
                    'recommendations' => $validation['recommendations'],
                    'checked_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Password validation failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Password validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get password breach details
     */
    public function getPasswordBreachDetails(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:1'
        ]);

        try {
            $breachDetails = app(PasswordBreachService::class)->getBreachDetails($request->password);
            
            return response()->json([
                'success' => true,
                'data' => $breachDetails
            ]);
        } catch (\Exception $e) {
            Log::error('Password breach details check failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Password breach check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
