<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailVerificationService
{
    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(User $user): bool
    {
        try {
            $token = $this->generateVerificationToken($user);
            
            Mail::send('emails.verify-email', [
                'user' => $user,
                'token' => $token,
                'verificationUrl' => route('verification.verify', [
                    'id' => $user->id,
                    'hash' => $token,
                ])
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email Address - ' . config('app.name'));
            });

            Log::info('Verification email sent', ['user_id' => $user->id, 'email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate secure verification token
     */
    protected function generateVerificationToken(User $user): string
    {
        $token = Str::random(64);
        
        // Store token in cache with expiration
        cache()->put(
            "email_verification_{$user->id}",
            $token,
            now()->addHours(24)
        );

        return $token;
    }

    /**
     * Verify email token
     */
    public function verifyToken(User $user, string $token): bool
    {
        $storedToken = cache()->get("email_verification_{$user->id}");
        
        if ($storedToken && hash_equals($storedToken, $token)) {
            $user->update([
                'email_verified_at' => now(),
                'email_verification_token' => null
            ]);
            
            cache()->forget("email_verification_{$user->id}");
            
            Log::info('Email verified successfully', ['user_id' => $user->id]);
            return true;
        }

        Log::warning('Invalid email verification token', ['user_id' => $user->id]);
        return false;
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        return $this->sendVerificationEmail($user);
    }
}
