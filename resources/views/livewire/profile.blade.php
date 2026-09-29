<div class="space-y-8">
    
    <!-- ═══════════ PROFILE & PASSWORD GRID ═══════════ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Profile Details Form -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">Profile Details</h3>
            
            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" wire:model.defer="name" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" wire:model.defer="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Mobile Number</label>
                    <input type="text" wire:model.defer="mobile_number" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm">
                    @error('mobile_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-md active:scale-[0.98] transition-all">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Password Change Form -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">Security Credentials</h3>
            
            <form wire:submit.prevent="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Current Password</label>
                    <input type="password" wire:model.defer="current_password" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm" placeholder="••••••••">
                    @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">New Password</label>
                    <input type="password" wire:model.defer="new_password" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm" placeholder="••••••••">
                    @error('new_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" wire:model.defer="new_password_confirmation" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm" placeholder="••••••••">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-md active:scale-[0.98] transition-all">
                    Change Password
                </button>
            </form>
        </div>
    </div>

    <!-- ═══════════ TWO-FACTOR AUTHENTICATION PANEL ═══════════ -->
    <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
        <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-2 flex items-center gap-2">
            <span>🛡️</span> Two-Factor Authentication (2FA)
        </h3>
        <p class="text-xs text-slate-400 mb-6">Secure your prescription analysis records by requiring a secondary verification code during login.</p>

        @if($isTwoFactorEnabled)
            <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-xs flex items-center justify-between mb-4">
                <span class="font-semibold flex items-center gap-1.5">
                    <span>✓</span> Two-Factor Authentication is currently active on your account.
                </span>
                <button type="button" wire:click="disable2FA" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold shadow-md">
                    Disable 2FA
                </button>
            </div>
        @elseif($show2FASetup)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 rounded-xl bg-slate-50/50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800/50">
                <!-- Instructions -->
                <div class="md:col-span-2 space-y-4 text-xs font-medium">
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">Configure Authenticator App</p>
                    <ol class="list-decimal pl-4 space-y-2 text-slate-600 dark:text-slate-400">
                        <li>Download any authenticator app like Google Authenticator or Microsoft Authenticator.</li>
                        <li>Scan the QR code, or manually input the secret key below.</li>
                        <li>Enter the 6-digit code generated by the app to confirm your setup.</li>
                    </ol>
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-mono text-center text-sm font-bold tracking-wider text-brand-primary">
                        Secret Key: {{ $twoFactorSecret }}
                    </div>
                </div>

                <!-- QR & Input -->
                <div class="flex flex-col items-center justify-center space-y-4">
                    <!-- Simulated QR Code placeholder -->
                    <div class="w-32 h-32 bg-slate-200 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center border border-slate-350 dark:border-slate-750 text-slate-400 font-mono text-[10px] text-center p-2">
                        <span>[MOCK QR]</span>
                        <span>mediscan-2fa-secret</span>
                    </div>

                    <form wire:submit.prevent="confirm2FA" class="w-full space-y-3">
                        <input type="text" wire:model.defer="twoFactorInput" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-center tracking-widest text-sm font-bold" placeholder="000000" maxlength="6">
                        @error('twoFactorInput') <span class="text-xs text-red-500 mt-1 block text-center">{{ $message }}</span> @enderror
                        <button type="submit" class="w-full py-2 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-md">
                            Confirm (Enter "123456")
                        </button>
                    </form>
                </div>
            </div>
        @else
            <button type="button" wire:click="initiate2FA" class="px-5 py-2.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-md active:scale-[0.98] transition-all">
                Setup 2FA Authenticator
            </button>
        @endif
    </div>

    <!-- ═══════════ SANCTUM API TOKENS MANAGER ═══════════ -->
    <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm space-y-6">
        <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-2 flex items-center gap-2">
            <span>🔌</span> Developer API Tokens
        </h3>
        <p class="text-xs text-slate-400">Generate personal access tokens to query the prescription scanning endpoints programmatically.</p>

        <!-- Generate Token Box -->
        <form wire:submit.prevent="generateToken" class="flex gap-3 items-end max-w-md">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Token Name</label>
                <input type="text" wire:model.defer="tokenName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-xs" placeholder="e.g. My Python Script">
                @error('tokenName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="py-3 px-5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-semibold text-xs shadow-md">
                Generate Token
            </button>
        </form>

        <!-- Show newly generated token -->
        @if($newTokenValue)
            <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-xs">
                <strong class="font-bold block mb-1">Generated API Token (Copy this now, it won't be shown again!):</strong>
                <code class="block p-3 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm font-mono break-all font-bold select-all">{{ $newTokenValue }}</code>
            </div>
        @endif

        <!-- Active Tokens Table -->
        <div class="overflow-x-auto pt-4 border-t border-slate-200/50 dark:border-slate-800/50">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/50 dark:border-slate-800/50 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="pb-3">Token Name</th>
                        <th class="pb-3">Last Used</th>
                        <th class="pb-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium">
                    @forelse($tokens as $tok)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                            <td class="py-3 font-semibold text-slate-900 dark:text-white">{{ $tok['name'] }}</td>
                            <td class="py-3 text-slate-400 font-mono">
                                {{ $tok['last_used_at'] ? date('M d, Y h:i A', strtotime($tok['last_used_at'])) : 'Never' }}
                            </td>
                            <td class="py-3 text-right">
                                <button type="button" wire:click="revokeToken({{ $tok['id'] }})" class="px-3 py-1.5 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-xl border border-red-500/20 transition-all font-bold">
                                    Revoke
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-400">
                                No developer tokens active.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
