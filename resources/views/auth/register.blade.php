<x-guest-layout>
      <!-- Single Card with Transparent Blur -->
        <div class="w-full max-w-md">
             
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-r from-indigo-500 via-purple-600 to-navy-600 shadow-lg mb-6">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold bg-gradient-to-r from-indigo-600 via-purple-600 to-navy-600 bg-clip-text text-transparent">
                        Create account
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Join us and start your journey today
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" class="text-gray-700 dark:text-gray-300 font-medium text-sm sm:text-base" />
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <x-text-input 
                                id="name" 
                                class="block w-full pl-10 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/30 dark:border-gray-600/30 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm sm:text-base" 
                                type="text" 
                                name="name" 
                                :value="old('name')" 
                                required 
                                autofocus 
                                autocomplete="name" 
                                placeholder="Enter your full name"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-700 dark:text-gray-300 font-medium text-sm sm:text-base" />
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <x-text-input 
                                id="email" 
                                class="block w-full pl-10 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/30 dark:border-gray-600/30 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm sm:text-base" 
                                type="email" 
                                name="email" 
                                :value="old('email')" 
                                required 
                                autocomplete="username" 
                                placeholder="Enter your email"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" class="text-gray-700 dark:text-gray-300 font-medium text-sm sm:text-base" />
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <x-text-input 
                                id="phone" 
                                class="block w-full pl-10 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/30 dark:border-gray-600/30 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm sm:text-base" 
                                type="tel" 
                                name="phone" 
                                :value="old('phone')" 
                                autocomplete="tel" 
                                placeholder="Enter your phone number"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-gray-700 dark:text-gray-300 font-medium text-sm sm:text-base" />
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <x-text-input 
                                id="password" 
                                class="block w-full pl-10 pr-12 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/30 dark:border-gray-600/30 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm sm:text-base" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Create a strong password"
                            />
                            <!-- Password Toggle Button -->
                            <button type="button" id="password-toggle" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg id="password-eye" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="password-eye-slash" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                            <!-- Password Strength Indicator -->
                            <div class="mt-2">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div id="password-strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <span id="password-strength-text" class="text-xs font-medium text-gray-500 dark:text-gray-400">Enter password</span>
                                </div>
                            </div>
                            
                            <!-- Real-time Password Feedback -->
                            <div id="password-feedback" class="mt-2 space-y-1 hidden">
                                <!-- Strength Feedback -->
                                <div id="strength-feedback" class="text-xs"></div>
                                
                                <!-- Breach Check Feedback -->
                                <div id="breach-feedback" class="text-xs"></div>
                                
                                <!-- Recommendations -->
                                <div id="password-recommendations" class="text-xs"></div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 dark:text-gray-300 font-medium text-sm sm:text-base" />
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <x-text-input 
                                id="password_confirmation" 
                                class="block w-full pl-10 pr-12 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/30 dark:border-gray-600/30 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm sm:text-base" 
                                type="password" 
                                name="password_confirmation" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Confirm your password"
                            />
                            <!-- Confirm Password Toggle Button -->
                            <button type="button" id="password-confirm-toggle" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg id="password-confirm-eye" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="password-confirm-eye-slash" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input id="terms" type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="terms" required>
                        <label for="terms" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            I agree to the 
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200">Terms of Service</a> 
                            and 
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm sm:text-base font-medium rounded-xl text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-navy-600 hover:from-indigo-700 hover:via-purple-700 hover:to-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-indigo-300 group-hover:text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </span>
                            Create account
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300/50 dark:border-gray-600/50"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-transparent text-gray-500">Or continue with</span>
                        </div>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="mt-6 flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 border border-white/30 dark:border-gray-600/30 transition-all duration-200 transform hover:scale-105 shadow-sm">
                        <svg class="w-6 h-6" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('social.redirect', 'facebook') }}" class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 border border-white/30 dark:border-gray-600/30 transition-all duration-200 transform hover:scale-105 shadow-sm">
                        <svg class="w-6 h-6" fill="#1877F2" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('social.redirect', 'github') }}" class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 border border-white/30 dark:border-gray-600/30 transition-all duration-200 transform hover:scale-105 shadow-sm">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                </div>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200">
                            Sign in
                        </a>
                    </p>
                </div>
            
        </div>
   

    <!-- Animation Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add entrance animations
            const card = document.querySelector('.backdrop-blur-xl');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px) scale(0.95)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.8s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            }, 100);
        });
    </script>

    <!-- Real-time Password Validation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const emailInput = document.getElementById('email');
            const nameInput = document.getElementById('name');
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            const feedbackContainer = document.getElementById('password-feedback');
            const strengthFeedback = document.getElementById('strength-feedback');
            const breachFeedback = document.getElementById('breach-feedback');
            const recommendations = document.getElementById('password-recommendations');
            
            // Password toggle elements
            const passwordToggle = document.getElementById('password-toggle');
            const passwordEye = document.getElementById('password-eye');
            const passwordEyeSlash = document.getElementById('password-eye-slash');
            const passwordConfirmToggle = document.getElementById('password-confirm-toggle');
            const passwordConfirmEye = document.getElementById('password-confirm-eye');
            const passwordConfirmEyeSlash = document.getElementById('password-confirm-eye-slash');
            
            let validationTimeout;
            let emailValidationTimeout;

            // Password strength colors
            const strengthColors = {
                0: 'bg-red-500',
                1: 'bg-red-500',
                2: 'bg-orange-500',
                3: 'bg-yellow-500',
                4: 'bg-blue-500',
                5: 'bg-green-500'
            };

            // Password strength labels
            const strengthLabels = {
                0: 'Very Weak',
                1: 'Very Weak',
                2: 'Weak',
                3: 'Fair',
                4: 'Good',
                5: 'Strong'
            };

            // Password toggle functionality
            function setupPasswordToggle(input, toggle, eye, eyeSlash) {
                toggle.addEventListener('click', function() {
                    const type = input.type;
                    if (type === 'password') {
                        input.type = 'text';
                        eye.classList.add('hidden');
                        eyeSlash.classList.remove('hidden');
                    } else {
                        input.type = 'password';
                        eye.classList.remove('hidden');
                        eyeSlash.classList.add('hidden');
                    }
                });
            }

            // Setup password toggles
            setupPasswordToggle(passwordInput, passwordToggle, passwordEye, passwordEyeSlash);
            setupPasswordToggle(confirmPasswordInput, passwordConfirmToggle, passwordConfirmEye, passwordConfirmEyeSlash);

            // Email validation
            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Username validation
            function validateUsername(username) {
                return username.length >= 2 && /^[a-zA-Z0-9\s]+$/.test(username);
            }

            // Real-time email validation
            function validateEmailField() {
                const email = emailInput.value.trim();
                const emailContainer = emailInput.closest('div').parentElement;
                let existingFeedback = emailContainer.querySelector('.email-feedback');
                
                if (!existingFeedback) {
                    existingFeedback = document.createElement('div');
                    existingFeedback.className = 'email-feedback mt-1 text-xs';
                    emailContainer.appendChild(existingFeedback);
                }

                if (!email) {
                    existingFeedback.innerHTML = '';
                    emailInput.classList.remove('border-red-500', 'border-green-500');
                    return;
                }

                if (!validateEmail(email)) {
                    existingFeedback.innerHTML = '<span class="text-red-500">⚠️ Please enter a valid email address</span>';
                    emailInput.classList.add('border-red-500');
                    emailInput.classList.remove('border-green-500');
                } else {
                    existingFeedback.innerHTML = '<span class="text-green-500">✅ Valid email format</span>';
                    emailInput.classList.add('border-green-500');
                    emailInput.classList.remove('border-red-500');
                }
            }

            // Real-time username validation
            function validateUsernameField() {
                const username = nameInput.value.trim();
                const nameContainer = nameInput.closest('div').parentElement;
                let existingFeedback = nameContainer.querySelector('.username-feedback');
                
                if (!existingFeedback) {
                    existingFeedback = document.createElement('div');
                    existingFeedback.className = 'username-feedback mt-1 text-xs';
                    nameContainer.appendChild(existingFeedback);
                }

                if (!username) {
                    existingFeedback.innerHTML = '';
                    nameInput.classList.remove('border-red-500', 'border-green-500');
                    return;
                }

                if (!validateUsername(username)) {
                    existingFeedback.innerHTML = '<span class="text-red-500">⚠️ Username must be at least 2 characters and contain only letters, numbers, and spaces</span>';
                    nameInput.classList.add('border-red-500');
                    nameInput.classList.remove('border-green-500');
                } else {
                    existingFeedback.innerHTML = '<span class="text-green-500">✅ Valid username format</span>';
                    nameInput.classList.add('border-green-500');
                    nameInput.classList.remove('border-red-500');
                }
            }

            // Check password strength locally
            function checkPasswordStrength(password) {
                let score = 0;
                const feedback = [];

                if (password.length >= 8) score++;
                else feedback.push('At least 8 characters');

                if (/[a-z]/.test(password)) score++;
                else feedback.push('Include lowercase letters');

                if (/[A-Z]/.test(password)) score++;
                else feedback.push('Include uppercase letters');

                if (/[0-9]/.test(password)) score++;
                else feedback.push('Include numbers');

                if (/[^A-Za-z0-9]/.test(password)) score++;
                else feedback.push('Include special characters');

                return { score, feedback };
            }

            // Update strength indicator
            function updateStrengthIndicator(score, feedback) {
                const percentage = (score / 5) * 100;
                strengthBar.style.width = percentage + '%';
                
                // Remove all color classes
                strengthBar.className = 'h-2 rounded-full transition-all duration-300 ' + strengthColors[score];
                
                strengthText.textContent = strengthLabels[score];
                strengthText.className = 'text-xs font-medium ' + 
                    (score <= 2 ? 'text-red-500' : 
                     score === 3 ? 'text-yellow-500' : 
                     score === 4 ? 'text-blue-500' : 'text-green-500');

                if (feedback.length > 0) {
                    strengthFeedback.innerHTML = '<span class="text-red-500">⚠️ ' + feedback.join(', ') + '</span>';
                } else {
                    strengthFeedback.innerHTML = '<span class="text-green-500">✅ Password strength is good</span>';
                }
            }

            // Check password breach via API
            async function checkPasswordBreach(password) {
                try {
                    const response = await fetch('/api/v1/validate-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ password: password })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        
                        if (data.breach_status && data.breach_status.compromised) {
                            const count = data.breach_status.count;
                            let severity = 'warning';
                            let icon = '⚠️';
                            
                            if (count > 1000) {
                                severity = 'danger';
                                icon = '🚨';
                            } else if (count > 100) {
                                severity = 'warning';
                                icon = '⚠️';
                            } else {
                                severity = 'info';
                                icon = 'ℹ️';
                            }

                            breachFeedback.innerHTML = `<span class="text-red-600 font-semibold">${icon} <strong>SECURITY ALERT:</strong> This password has been found in <strong>${count.toLocaleString()}</strong> data breaches! <strong>DO NOT USE THIS PASSWORD!</strong></span>`;
                            
                            // Add visual warning to password input
                            passwordInput.classList.add('border-red-500', 'ring-red-500', 'ring-2');
                            passwordInput.classList.remove('border-green-500', 'ring-green-500', 'border-white/30');
                            
                            // Add shake animation for critical breaches
                            if (count > 1000) {
                                passwordInput.classList.add('animate-pulse');
                                setTimeout(() => {
                                    passwordInput.classList.remove('animate-pulse');
                                }, 2000);
                            }
                        } else {
                            breachFeedback.innerHTML = '<span class="text-green-500 font-semibold">✅ Password not found in any known breaches</span>';
                            
                            // Remove warning styling if password is safe
                            passwordInput.classList.remove('border-red-500', 'ring-red-500', 'ring-2', 'animate-pulse');
                            if (password.length >= 8) {
                                passwordInput.classList.add('border-green-500', 'ring-green-500');
                            }
                        }

                        // Show recommendations
                        if (data.recommendations && data.recommendations.length > 0) {
                            recommendations.innerHTML = '<span class="text-blue-500">💡 ' + data.recommendations.join(' ') + '</span>';
                        } else {
                            recommendations.innerHTML = '<span class="text-green-500">✅ Password meets security requirements</span>';
                        }
                    }
                } catch (error) {
                    console.error('Error checking password breach:', error);
                    breachFeedback.innerHTML = '<span class="text-gray-500">ℹ️ Unable to check password breach status</span>';
                }
            }

            // Main validation function
            function validatePassword(password) {
                if (!password) {
                    feedbackContainer.classList.add('hidden');
                    strengthBar.style.width = '0%';
                    strengthText.textContent = 'Enter password';
                    strengthText.className = 'text-xs font-medium text-gray-500 dark:text-gray-400';
                    return;
                }

                // Show feedback container
                feedbackContainer.classList.remove('hidden');

                // Check strength locally
                const strength = checkPasswordStrength(password);
                updateStrengthIndicator(strength.score, strength.feedback);

                // Check breach status (debounced)
                clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => {
                    if (password.length >= 3) { // Only check if password is long enough
                        checkPasswordBreach(password);
                    }
                }, 500);
            }

            // Event listeners
            passwordInput.addEventListener('input', function() {
                validatePassword(this.value);
            });

            passwordInput.addEventListener('focus', function() {
                if (this.value) {
                    feedbackContainer.classList.remove('hidden');
                }
            });

            // Email validation event listeners
            emailInput.addEventListener('input', function() {
                clearTimeout(emailValidationTimeout);
                emailValidationTimeout = setTimeout(validateEmailField, 300);
            });

            emailInput.addEventListener('blur', validateEmailField);

            // Username validation event listeners
            nameInput.addEventListener('input', function() {
                validateUsernameField();
            });

            nameInput.addEventListener('blur', validateUsernameField);

            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    this.setCustomValidity('Passwords do not match');
                    this.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                }
            });

            // Form submission validation
            const registerForm = document.querySelector('form[action*="register"]');
            registerForm.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Check if passwords match
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('❌ Passwords do not match. Please check your password confirmation.');
                    return false;
                }
                
                // Check if password is too weak
                const strength = checkPasswordStrength(password);
                if (strength.score < 3) {
                    e.preventDefault();
                    alert('❌ Password is too weak. Please choose a stronger password with at least 8 characters, including uppercase, lowercase, numbers, and special characters.');
                    return false;
                }
                
                // Check if there's a breach warning visible
                const breachWarning = document.getElementById('breach-feedback');
                if (breachWarning && breachWarning.innerHTML.includes('SECURITY ALERT')) {
                    e.preventDefault();
                    alert('🚨 SECURITY ALERT: This password has been compromised in data breaches. Please choose a different password for your security.');
                    return false;
                }
                
                // Note: Server-side validation will provide additional security
            });
        });
    </script>
</x-guest-layout>
