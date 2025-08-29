<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Show login page
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Show register page
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Show OTP login page
     */
    public function showOtpLogin(): View
    {
        return view('auth.otp-login');
    }

    /**
     * Show OTP verification page
     */
    public function showOtpVerification(Request $request): View
    {
        return view('auth.otp-verification', [
            'email' => $request->email
        ]);
    }

    /**
     * Handle login
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $user = $this->authService->login($request->validated());
            
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('customer.dashboard'));
            }
        } catch (\Exception $e) {
            Log::error('Web login failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);

            return back()->withErrors([
                'email' => $e->getMessage(),
            ])->withInput($request->only('email'));
        }
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('customer.dashboard')->with('success', 'Registration successful!');
        } catch (\Exception $e) {
            Log::error('Web registration failed', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors([
                'error' => $e->getMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Send OTP
     */
    public function sendOtp(SendOtpRequest $request): RedirectResponse
    {
        try {
            $this->authService->sendOtp($request->email);

            return redirect()->route('auth.otp.verification', ['email' => $request->email])
                ->with('success', 'OTP sent successfully!');
        } catch (\Exception $e) {
            Log::error('Web OTP send failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return back()->withErrors([
                'email' => $e->getMessage(),
            ])->withInput($request->only('email'));
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(OtpRequest $request): RedirectResponse
    {
        try {
            $user = $this->authService->verifyOtp($request->email, $request->otp);
            
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('customer.dashboard'));
            }
        } catch (\Exception $e) {
            Log::error('Web OTP verification failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return back()->withErrors([
                'otp' => $e->getMessage(),
            ])->withInput($request->only('email'));
        }
    }

    /**
     * Handle social login redirect
     */
    public function socialRedirect(string $provider): RedirectResponse
    {
        try {
            return \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Social redirect failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Social login not available.',
            ]);
        }
    }

    /**
     * Handle social login callback
     */
    public function socialCallback(string $provider, Request $request): RedirectResponse
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
            
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('customer.dashboard'));
            }
        } catch (\Exception $e) {
            Log::error('Social callback failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Social login failed.',
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request): RedirectResponse
    {
        try {
            $this->authService->revokeAllTokens($request->user());
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Logged out successfully!');
        } catch (\Exception $e) {
            Log::error('Web logout failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return redirect()->route('login');
        }
    }
}
