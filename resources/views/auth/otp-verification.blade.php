<x-guest-layout>

   <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
                    Verify OTP
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter the 6-digit code sent to your email
                </p>
                @if(isset($email))
                    <p class="mt-1 text-sm text-indigo-600 dark:text-indigo-400">
                        {{ $email }}
                    </p>
                @endif
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- OTP Verification Form -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8 transform transition-all duration-300 hover:scale-105">
                <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-6">
                    @csrf

                    <!-- Hidden Email Field -->
                    <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

                    <!-- OTP Input -->
                    <div>
                        <x-input-label for="otp" :value="__('OTP Code')" class="text-gray-700 dark:text-gray-300" />
                        <div class="mt-1">
                            <x-text-input 
                                id="otp" 
                                class="block w-full text-center text-2xl font-mono tracking-widest transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                type="text" 
                                name="otp" 
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required 
                                autofocus 
                                autocomplete="one-time-code"
                                placeholder="000000"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Enter the 6-digit code sent to your email
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            Verify OTP
                        </button>
                    </div>
                </form>

                <!-- Resend OTP -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Didn't receive the code? 
                        <form method="POST" action="{{ route('auth.otp.send') }}" class="inline">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                            <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200">
                                Resend OTP
                            </button>
                        </form>
                    </p>
                </div>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500">Or</span>
                        </div>
                    </div>
                </div>

                <!-- Alternative Options -->
                <div class="mt-6 space-y-4">
                    <a href="{{ route('login') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Login with Password
                    </a>
                </div>
            </div>
        </div>
   

    <!-- Animation Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add entrance animations
            const elements = document.querySelectorAll('.transform');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Auto-focus and format OTP input
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.addEventListener('input', function(e) {
                    // Remove non-numeric characters
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Limit to 6 digits
                    if (this.value.length > 6) {
                        this.value = this.value.slice(0, 6);
                    }
                });

                otpInput.addEventListener('keypress', function(e) {
                    // Allow only numeric keys
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</x-guest-layout>
