<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private PasswordBreachService $passwordBreachService
    ) {}

    /**
     * Register a new user with enhanced password validation
     */
    public function register(array $data): User
    {
        // Enhanced password validation with real-time breach checking
        $passwordValidation = $this->passwordBreachService->validatePassword($data['password']);
        
        if (!$passwordValidation['is_safe']) {
            $errorMessage = 'Password security issues found: ' . implode('; ', $passwordValidation['recommendations']);
            
            Log::warning('Registration blocked due to password security issues', [
                'email' => $data['email'],
                'strength_score' => $passwordValidation['strength']['score'],
                'breach_compromised' => $passwordValidation['breach_status']['compromised'],
                'breach_count' => $passwordValidation['breach_status']['count']
            ]);
            
            throw ValidationException::withMessages([
                'password' => $errorMessage
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        // Assign default customer role
        $user->assignRole('Customer');

        Log::info('User registered successfully', [
            'user_id' => $user->id, 
            'email' => $user->email,
            'password_strength' => $passwordValidation['strength']['strength']
        ]);

        return $user;
    }

    /**
     * Validate password for password change
     */
    public function validatePasswordChange(string $newPassword): array
    {
        $validation = $this->passwordBreachService->validatePassword($newPassword);
        
        if (!$validation['is_safe']) {
            Log::warning('Password change validation failed', [
                'strength_score' => $validation['strength']['score'],
                'breach_compromised' => $validation['breach_status']['compromised'],
                'breach_count' => $validation['breach_status']['count']
            ]);
        }

        return $validation;
    }

    /**
     * Send OTP for email verification
     */
    public function sendOtp(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.'
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact support.'
            ]);
        }

        $otp = $user->generateOtp();

        // In a real application, you would send this via email
        // For now, we'll log it for development
        Log::info('OTP generated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp
        ]);

        return true;
    }

    /**
     * Verify OTP and login user
     */
    public function verifyOtp(string $email, string $otp): User
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.'
            ]);
        }

        if (!$user->isOtpValid($otp)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP. Please request a new one.'
            ]);
        }

        // Clear OTP after successful verification
        $user->clearOtp();
        $user->updateLastLogin();

        // Log the successful login
        Log::info('User logged in via OTP', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return $user;
    }

    /**
     * Login with password
     */
    public function login(array $credentials): User
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.'
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact support.'
            ]);
        }

        $user->updateLastLogin();

        Log::info('User logged in with password', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return $user;
    }

    /**
     * Create API token for user
     */
    public function createApiToken(User $user, string $tokenName = 'auth-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke all tokens for user
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Handle social login
     */
    public function handleSocialLogin(array $socialData, string $provider): User
    {
        $user = User::where('social_id', $socialData['id'])
            ->where('social_provider', $provider)
            ->first();

        if (!$user) {
            // Check if user exists with same email
            $user = User::where('email', $socialData['email'])->first();

            if ($user) {
                // Update existing user with social data
                $user->update([
                    'social_id' => $socialData['id'],
                    'social_provider' => $provider,
                    'avatar' => $socialData['avatar'] ?? null,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialData['name'],
                    'email' => $socialData['email'],
                    'social_id' => $socialData['id'],
                    'social_provider' => $provider,
                    'avatar' => $socialData['avatar'] ?? null,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                // Assign default customer role
                $user->assignRole('Customer');
            }
        }

        $user->updateLastLogin();

        Log::info('User logged in via social', [
            'user_id' => $user->id,
            'provider' => $provider,
            'email' => $user->email
        ]);

        return $user;
    }
}
