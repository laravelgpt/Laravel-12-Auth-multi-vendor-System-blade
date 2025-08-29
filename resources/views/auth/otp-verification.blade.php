<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - OTP Verification</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-navy-900 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-navy-500/10 rounded-full blur-3xl animate-pulse delay-500"></div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-2xl mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Verify OTP</h1>
                    <p class="text-indigo-200/80 text-sm">Enter the 6-digit code sent to your email</p>
                </div>

                <!-- OTP Verification Card -->
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-6">
                        @csrf

                        <!-- Email (Hidden) -->
                        <input type="hidden" name="email" value="{{ $email }}">

                        <!-- OTP Input -->
                        <div>
                            <x-input-label for="otp" :value="__('One-Time Password')" class="text-white/90 font-medium text-sm" />
                            <div class="mt-2 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <x-text-input 
                                    id="otp" 
                                    class="block w-full pl-10 bg-white/10 border border-white/20 text-white placeholder-indigo-200/60 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 rounded-xl transition-all duration-200 text-center text-lg font-mono tracking-widest" 
                                    type="text" 
                                    name="otp" 
                                    required 
                                    autofocus 
                                    maxlength="6" 
                                    pattern="[0-9]{6}" 
                                    placeholder="000000"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                        </div>

                        <!-- Verify Button -->
                        <div>
                            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-transparent shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Verify OTP') }}
                            </button>
                        </div>

                        <!-- Resend OTP Section -->
                        <div class="text-center">
                            <p class="text-white/60 text-sm mb-3">
                                Didn't receive the code?
                            </p>
                            <form method="POST" action="{{ route('auth.otp.resend') }}" class="inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" class="text-indigo-300 hover:text-indigo-200 font-medium transition-colors duration-200">
                                    Resend OTP
                                </button>
                            </form>
                        </div>

                        <!-- Divider -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-white/20"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-transparent text-white/60">Or</span>
                            </div>
                        </div>

                        <!-- Alternative Options -->
                        <div class="space-y-3">
                            <a href="{{ route('login') }}" class="w-full flex justify-center items-center px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Back to Login
                            </a>
                        </div>

                        <!-- Info Text -->
                        <div class="text-center">
                            <p class="text-white/60 text-xs">
                                The OTP will expire in 10 minutes for security reasons.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus and format OTP input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            
            // Auto-focus on load
            otpInput.focus();
            
            // Format input to only allow numbers
            otpInput.addEventListener('input', function(e) {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limit to 6 digits
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Handle paste events
            otpInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = pastedText.replace(/[^0-9]/g, '').slice(0, 6);
                this.value = numbers;
                
                if (numbers.length === 6) {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>
