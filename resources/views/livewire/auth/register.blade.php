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
            </div>
            <h2 class="font-display font-extrabold text-3xl tracking-tight text-slate-900 dark:text-white">Create Account</h2>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-widest">Join MediScan AI Portal</p>
        </div>

        <!-- Registration Form -->
        <form wire:submit.prevent="register" class="space-y-5 relative z-10">
            
            <!-- Full Name -->
            <div class="space-y-1">
                <label for="name" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Full Name</label>
                <div class="relative rounded-2xl shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">👤</span>
                    <input type="text" id="name" wire:model.defer="name" 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                           placeholder="John Doe" required autofocus autocomplete="name">
                </div>
                @error('name') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- Email Address -->
            <div class="space-y-1">
                <label for="email" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email Address</label>
                <div class="relative rounded-2xl shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">✉️</span>
                    <input type="email" id="email" wire:model.defer="email" 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                           placeholder="john@example.com" required autocomplete="email">
                </div>
                @error('email') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- Mobile Number -->
            <div class="space-y-1">
                <label for="mobile_number" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Mobile Number (Optional)</label>
                <div class="relative rounded-2xl shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">📞</span>
                    <input type="text" id="mobile_number" wire:model.defer="mobile_number" 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                           placeholder="e.g. +1234567890" autocomplete="tel">
                </div>
                @error('mobile_number') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1">
                <label for="password" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Password</label>
                <div class="relative rounded-2xl shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">🔒</span>
                    <input type="password" id="password" wire:model.defer="password" 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                           placeholder="••••••••" required autocomplete="new-password">
                </div>
                @error('password') <span class="text-xs text-red-500 mt-1 block font-medium">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-1">
                <label for="password_confirmation" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Confirm Password</label>
                <div class="relative rounded-2xl shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-sm">🔒</span>
                    <input type="password" id="password_confirmation" wire:model.defer="password_confirmation" 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600" 
                           placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-4 bg-brand-primary hover:bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2 text-sm mt-2">
                <span>📝</span> Register & Allocate Credits
            </button>
        </form>

        <!-- Login Redirect Link -->
        <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 relative z-10">
            Already have an account? 
            <a href="{{ url('login') }}" wire:navigate class="text-brand-primary hover:underline font-bold ml-1 transition-colors hover:text-blue-600">Login here</a>
        </div>
    </div>
</div>
