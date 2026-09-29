<div class="relative min-h-[90vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <!-- Floating Minimalist Theme Toggle at Top Right -->
    <div class="absolute top-6 right-6 z-20">
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                class="p-3 rounded-2xl glass-card bg-white/40 dark:bg-slate-900/40 border border-slate-200/40 dark:border-slate-800/40 hover:bg-white/60 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300 shadow-md transition-all active:scale-95 duration-200">
            <span x-show="!darkMode" x-cloak class="text-sm font-semibold flex items-center gap-1.5">🌙 Dark Mode</span>
            <span x-show="darkMode" class="text-sm font-semibold flex items-center gap-1.5">☀️ Light Mode</span>
        </button>
    </div>

    <!-- Main Card Container -->
    <div class="w-full max-w-md p-8 sm:p-10 rounded-3xl glass-card bg-white/50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800/50 shadow-2xl relative overflow-hidden group hover:border-brand-primary/20 transition-all duration-500">
        
        <!-- Glowing gradient effect inside card -->
        <div class="absolute -top-20 -left-20 w-48 h-48 bg-brand-primary/10 rounded-full blur-3xl pointer-events-none group-hover:bg-brand-primary/15 transition-all duration-500"></div>
        <div class="absolute -bottom-20 -right-20 w-48 h-48 bg-brand-secondary/10 rounded-full blur-3xl pointer-events-none group-hover:bg-brand-secondary/15 transition-all duration-500"></div>

        <!-- Logo and Heading -->
        <div class="text-center mb-8 relative z-10">
            <div class="inline-block relative">
                <img src="{{ asset('brand_logo.png') }}" alt="MediScan Logo" 
                     class="w-20 h-20 mx-auto rounded-3xl shadow-xl border border-brand-primary/20 p-1 bg-white/80 dark:bg-slate-950/80 mb-5 transform hover:rotate-3 hover:scale-105 transition-all duration-300">
                <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500 border-2 border-white dark:border-slate-900"></span>
                </span>
            </div>
            <h2 class="font-display font-extrabold text-3xl tracking-tight text-slate-900 dark:text-white">Welcome back</h2>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-widest">Enterprise Prescription Engine</p>
        </div>

        @if($showTwoFactor)
            <!-- ═══════════ TWO-FACTOR VERIFICATION SCREEN ═══════════ -->
            <form wire:submit.prevent="verifyTwoFactor" class="space-y-6 relative z-10">
                <div class="space-y-2 text-center mb-4">
                    <span class="text-3xl">🛡️</span>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">Two-Factor Authentication</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Please enter the security verification code from your authenticator app.</p>
                </div>

                <div class="space-y-1">
                    <label for="two_factor_code" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">6-Digit Code</label>
                    <div class="relative rounded-2xl shadow-sm">
                        <input type="text" id="two_factor_code" wire:model.defer="two_factor_code" 
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-center tracking-[0.75em] text-xl font-extrabold focus:border-brand-primary transition-all placeholder-slate-300 dark:placeholder-slate-700" 
                               placeholder="000000" maxlength="6" autofocus autocomplete="one-time-code">
                    </div>
                    @error('two_factor_code') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-2.5 block text-center leading-relaxed">Authenticator code required. Use <strong class="text-brand-primary">123456</strong> for simulated verification bypass.</span>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-brand-primary hover:bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2">
                    Verify & Access Portal
                </button>
            </form>
        @else
            <!-- ═══════════ AUTHENTICATION FORM SCREEN ═══════════ -->
            <!-- Glassmorphic Tabs Selection -->
            <div class="flex p-1 rounded-2xl bg-slate-100/50 dark:bg-slate-950/50 border border-slate-200/30 dark:border-slate-800/30 mb-8 relative z-10">
                <button wire:click="setMode('credentials')" 
                        class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center gap-1.5 {{ $loginMode === 'credentials' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>🔑</span> Credentials
                </button>
                <button wire:click="setMode('otp')" 
                        class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center gap-1.5 {{ $loginMode === 'otp' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>📲</span> Mobile OTP
                </button>
            </div>

            @if($loginMode === 'credentials')
                <!-- Email/Password Login Form -->
                <form wire:submit.prevent="login" class="space-y-6 relative z-10">
                    <div class="space-y-1">
                        <label for="email" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email Address</label>
                        <div class="relative rounded-2xl shadow-sm">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">✉️</span>
                            <input type="email" id="email" wire:model.defer="email" 
                                   class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                                   placeholder="e.g. admin@mediscan.com" required autocomplete="email">
                        </div>
                        @error('email') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Password</label>
                        </div>
                        <div class="relative rounded-2xl shadow-sm">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">🔒</span>
                            <input type="password" id="password" wire:model.defer="password" 
                                   class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                                   placeholder="••••••••" required autocomplete="current-password">
                        </div>
                        @error('password') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
                    </div>

                    <button type="submit" 
                            class="w-full py-4 bg-brand-primary hover:bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                        <span>🔓</span> Authenticate Securely
                    </button>
                </form>
            @else
                <!-- OTP Login Form -->
                @if($otpStep === 'request')
                    <form wire:submit.prevent="sendOtp" class="space-y-6 relative z-10">
                        <div class="space-y-1">
                            <label for="mobile_number" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Mobile Number</label>
                            <div class="relative rounded-2xl shadow-sm">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">📞</span>
                                <input type="text" id="mobile_number" wire:model.defer="mobile_number" 
                                       class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                                       placeholder="+1234567890" required autocomplete="tel">
                            </div>
                            @error('mobile_number') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
                        </div>
                        <button type="submit" 
                                class="w-full py-4 bg-brand-primary hover:bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <span>📲</span> Send OTP Verification Code
                        </button>
                    </form>
                @else
                    <form wire:submit.prevent="verifyOtp" class="space-y-6 relative z-10">
                        <div class="space-y-1">
                            <label for="otp_input" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Enter OTP Code</label>
                            <div class="relative rounded-2xl shadow-sm">
                                <input type="text" id="otp_input" wire:model.defer="otp_input" 
                                       class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-center tracking-[0.75em] text-xl font-extrabold placeholder-slate-300 dark:placeholder-slate-700" 
                                       placeholder="000000" maxlength="6" required autofocus autocomplete="one-time-code">
                            </div>
                            @error('otp_input') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 block text-center leading-relaxed">Check SMS/Logs for details. Use <strong class="text-brand-primary">123456</strong> for mock override.</span>
                        </div>
                        <button type="submit" 
                                class="w-full py-4 bg-brand-primary hover:bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            Confirm & Access Portal
                        </button>
                    </form>
                @endif
            @endif

            <!-- ═══════════ MOCK SOCIAL LOGIN BUTTONS ═══════════ -->
            <div class="relative my-8 text-center relative z-10">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-slate-200/50 dark:border-slate-800/50"></span>
                </div>
                <span class="relative bg-[#F8FAFC] dark:bg-[#0B132B] px-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Or authenticate via</span>
            </div>

            <div class="grid grid-cols-3 gap-3 relative z-10">
                <button type="button" wire:click="loginWithSocial('google')" 
                        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border border-slate-200/50 dark:border-slate-800/50 bg-white/40 dark:bg-slate-950/40 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300 transition-all text-xs font-bold shadow-sm active:scale-95 duration-200">
                    <span class="text-sm">🌐</span> Google
                </button>
                <button type="button" wire:click="loginWithSocial('facebook')" 
                        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border border-slate-200/50 dark:border-slate-800/50 bg-white/40 dark:bg-slate-950/40 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300 transition-all text-xs font-bold shadow-sm active:scale-95 duration-200">
                    <span class="text-sm">👥</span> Facebook
                </button>
                <button type="button" wire:click="loginWithSocial('apple')" 
                        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border border-slate-200/50 dark:border-slate-800/50 bg-white/40 dark:bg-slate-950/40 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300 transition-all text-xs font-bold shadow-sm active:scale-95 duration-200">
                    <span class="text-sm">🍎</span> Apple
                </button>
            </div>

            <!-- Register Redirect Link -->
            <div class="mt-10 text-center text-xs text-slate-500 dark:text-slate-400 relative z-10">
                Don't have an account? 
                <a href="{{ url('register') }}" wire:navigate class="text-brand-primary hover:underline font-bold ml-1 transition-colors hover:text-blue-600">Register here</a>
            </div>
        @endif
    </div>
</div>
