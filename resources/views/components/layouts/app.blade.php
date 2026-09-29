<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MediScan AI 2.0' }}</title>
    
    <!-- PWA Manifest & Meta -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563EB">
    <link rel="apple-touch-icon" href="{{ asset('brand_logo.png') }}">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-light-bg dark:bg-dark-bg text-slate-800 dark:text-slate-200 min-h-screen font-sans relative overflow-x-hidden antialiased">
    <!-- Ambient glowing orbs -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="orb w-96 h-96 bg-brand-primary/20 dark:bg-brand-primary/10 top-[-10%] left-[-10%]"></div>
        <div class="orb w-[30rem] h-[30rem] bg-brand-secondary/20 dark:bg-brand-secondary/10 bottom-[-10%] right-[-10%]"></div>
        <div class="orb w-80 h-80 bg-brand-accent/20 dark:bg-brand-accent/10 top-[40%] left-[50%] translate-x-[-50%]"></div>
    </div>
 
    <div class="flex min-h-screen relative z-10" x-data="{ sidebarOpen: false }">
        @auth
            <!-- ═══════════ SIDEBAR ═══════════ -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 w-64 glass-card bg-white/70 dark:bg-slate-900/75 border-r border-slate-200/50 dark:border-slate-800/50 z-30 transition-transform duration-300 md:translate-x-0 md:static md:flex md:flex-col">
                <!-- Brand Logo -->
                <div class="p-6 flex items-center gap-3 border-b border-slate-200/50 dark:border-slate-800/50">
                    <img src="{{ asset('brand_logo.png') }}" alt="MediScan Logo" class="w-10 h-10 rounded-xl shadow-lg border border-brand-primary/20">
                    <div>
                        <h1 class="font-display font-bold text-lg leading-tight tracking-tight text-slate-900 dark:text-white">
                            MediScan <span class="text-brand-primary">AI 2.0</span>
                        </h1>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Enterprise Healthcare</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    <!-- Standard User Routes -->
                    <a href="{{ url('dashboard') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('dashboard') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>📊</span> Dashboard
                    </a>
                    <a href="{{ url('scanner') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('scanner') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>📷</span> Prescription Scan
                    </a>
                    <a href="{{ url('explorer') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('explorer') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>🔍</span> Medicine Explorer
                    </a>
                    <a href="{{ url('drug-checker') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('drug-checker') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>🛡️</span> Drug Checker
                    </a>
                    <a href="{{ url('ai-assistant') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('ai-assistant') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>💬</span> AI Assistant
                    </a>
                    <a href="{{ url('profile') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->is('profile') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <span>⚙️</span> Settings & 2FA
                    </a>
 
                    <!-- Admin Route (Admin only) -->
                    @if(auth()->user()->isAdmin())
                        <div class="pt-4 mt-4 border-t border-slate-200/50 dark:border-slate-800/50">
                            <span class="px-4 text-xs font-bold text-slate-400 tracking-wider uppercase">Administration</span>
                            <a href="{{ url('admin') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 mt-2 rounded-xl text-sm font-medium transition-all {{ request()->is('admin*') ? 'bg-brand-secondary text-white shadow-lg shadow-brand-secondary/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }}">
                                <span>🛠️</span> Admin Panel
                            </a>
                        </div>
                    @endif
                </nav>

                <!-- Sidebar Footer / Logout -->
                <div class="p-4 border-t border-slate-200/50 dark:border-slate-800/50">
                    <form method="POST" action="{{ url('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50/50 dark:hover:bg-red-950/20 transition-all text-left">
                            <span>🚪</span> Logout
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Overlay to close sidebar on mobile -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 md:hidden" x-cloak></div>
        @endauth

        <!-- ═══════════ MAIN VIEWPORT ═══════════ -->
        <div class="flex-1 flex flex-col min-w-0">
            @auth
                <!-- Header -->
                <header class="h-16 glass-card bg-white/70 dark:bg-slate-900/75 border-b border-slate-200/50 dark:border-slate-800/50 px-4 md:px-8 flex items-center justify-between sticky top-0 z-20">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 md:hidden text-slate-600 dark:text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <span class="font-display font-semibold hidden md:block text-slate-900 dark:text-white">MediScan Portal</span>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Dynamic Credit Badge -->
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 animate-pulse-slow">
                            <span>💎</span> <span id="credits-display">{{ auth()->user()->credits }}</span> Credits
                        </div>

                        <!-- Theme Toggle -->
                        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-xl border border-slate-200/50 dark:border-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
                            <span x-show="!darkMode" x-cloak>🌙</span>
                            <span x-show="darkMode">☀️</span>
                        </button>

                        <!-- User Avatar -->
                        <div class="flex items-center gap-3 border-l border-slate-200/50 dark:border-slate-800/50 pl-4">
                            <div class="w-8 h-8 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium hidden lg:block text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </header>
            @endauth

            <!-- Main Content Container -->
            <main class="flex-1 p-4 md:p-8 overflow-y-auto">
                <!-- Notifications/Alerts -->
                @if (session()->has('message'))
                    <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-sm flex items-center gap-2">
                        <span>✅</span> {{ session('message') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm flex items-center gap-2">
                        <span>⚠️</span> {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Livewire scripts -->
    @livewireScripts

    <!-- Toast Notification Handler -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
    <script>
        window.showToast = function(msg, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-xl shadow-lg border backdrop-blur-md transition-all duration-300 transform translate-y-2 opacity-0 flex items-center gap-2.5 text-sm font-medium ${
                type === 'success' 
                ? 'bg-green-500/10 border-green-500/20 text-green-500' 
                : type === 'error'
                ? 'bg-red-500/10 border-red-500/20 text-red-500'
                : 'bg-brand-primary/10 border-brand-primary/20 text-brand-primary'
            }`;
            toast.innerHTML = `<span>${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span> ${msg}`;
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            // Remove toast
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        };

        // Livewire Toast Listener
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (event) => {
                window.showToast(event[0].message, event[0].type || 'success');
            });
            Livewire.on('credits-updated', (event) => {
                const el = document.getElementById('credits-display');
                if (el) el.textContent = event[0].credits;
            });
        });

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                    .then(reg => console.log('Service Worker Registered!', reg))
                    .catch(err => console.log('Service Worker registration failed', err));
            });
        }
    </script>
</body>
</html>
