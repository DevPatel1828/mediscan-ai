<div class="space-y-8">
    
    <!-- ═══════════ ANALYTICS STATS GRID ═══════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Users -->
        <div class="p-5 rounded-xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Users</span>
            <span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white mt-1 block">{{ $userCount }}</span>
        </div>
        <!-- Scans -->
        <div class="p-5 rounded-xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Scans</span>
            <span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white mt-1 block">{{ $scanCount }}</span>
        </div>
        <!-- Medicines -->
        <div class="p-5 rounded-xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Medicines</span>
            <span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white mt-1 block">{{ $totalMeds }}</span>
        </div>
        <!-- Accuracy -->
        <div class="p-5 rounded-xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">OCR Accuracy</span>
            <span class="font-display font-extrabold text-2xl text-green-500 mt-1 block">{{ $averageConfidence }}%</span>
        </div>
        <!-- Server Status -->
        <div class="p-5 rounded-xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm" x-data="{ online: false }" x-init="fetch('{{ env('FASTAPI_URL', 'http://localhost:8000') }}/api/health').then(r => r.json()).then(d => online = d.status === 'ok').catch(() => online = false)">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">AI Core Server</span>
            <div class="flex items-center gap-1.5 mt-1.5">
                <span class="w-3 h-3 rounded-full animate-pulse-slow" :class="online ? 'bg-green-500' : 'bg-red-500'"></span>
                <span class="font-bold text-sm" :class="online ? 'text-green-500' : 'text-red-500'" x-text="online ? 'ONLINE' : 'OFFLINE'"></span>
            </div>
        </div>
    </div>

    <!-- ═══════════ MAIN SECTIONS GRID ═══════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: User Management Table -->
        <div class="lg:col-span-2 p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">User Directories</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200/50 dark:border-slate-800/50 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="pb-3">User</th>
                            <th class="pb-3">Credits</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium">
                        @foreach($users as $usr)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                <td class="py-3">
                                    <p class="font-bold text-slate-850 dark:text-white text-sm">{{ $usr['name'] }}</p>
                                    <span class="text-[10px] text-slate-400">{{ $usr['email'] }}</span>
                                </td>
                                <td class="py-3 font-mono font-bold">{{ $usr['credits'] }}</td>
                                <td class="py-3">
                                    @if(in_array($usr['id'], $bannedUsers))
                                        <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-500 font-bold border border-red-500/20">Banned</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-500 font-bold border border-green-500/20">Active</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right space-x-2">
                                    <button type="button" wire:click="resetCredits({{ $usr['id'] }})" class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all font-semibold">
                                        Reset Credits
                                    </button>
                                    <button type="button" wire:click="toggleBan({{ $usr['id'] }})" class="px-2.5 py-1.5 rounded-xl border transition-all font-semibold {{
                                        in_array($usr['id'], $bannedUsers) 
                                        ? 'bg-green-500/10 border-green-500/20 text-green-500 hover:bg-green-500 hover:text-white' 
                                        : 'bg-red-500/10 border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white'
                                    }}">
                                        {{ in_array($usr['id'], $bannedUsers) ? 'Unban' : 'Ban' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Medicine Import & Templates -->
        <div class="space-y-6">
            <!-- CSV Bulk Import Card -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm space-y-4">
                <h3 class="font-display font-bold text-md text-slate-900 dark:text-white">Bulk Import Medicines</h3>
                <p class="text-xs text-slate-400">Import a CSV file to bulk seed the 50,000+ medicine database records instantly.</p>
                
                <form wire:submit.prevent="importCsv" class="space-y-4">
                    <div class="border border-dashed border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center cursor-pointer hover:border-brand-primary transition-all relative">
                        <input type="file" wire:model="csvFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".csv">
                        <div class="text-2xl mb-1">📁</div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ $csvFile ? $csvFile->getClientOriginalName() : 'Choose CSV file' }}
                        </span>
                    </div>
                    @error('csvFile') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror

                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button type="button" wire:click="downloadTemplate" class="px-3 py-2 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400">
                            Template CSV ⬇️
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-md">
                            Upload & Import
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Logs -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <h3 class="font-display font-bold text-md text-slate-900 dark:text-white mb-3">System Log View</h3>
                <div class="bg-slate-950 p-4 rounded-xl font-mono text-[10px] text-green-400 border border-slate-800 h-40 overflow-y-auto leading-relaxed">
                    <p class="text-slate-500">[2026-06-05 16:11:02] Scheduler running credit daily resets...</p>
                    <p class="text-slate-500">[2026-06-05 16:11:03] daily_reset: completed successfully (2 users)</p>
                    <p class="text-slate-500">[2026-06-05 16:12:44] Database: mediscan_db online on port 3306</p>
                    <p class="text-slate-500">[2026-06-05 16:14:15] Python: FastAPI WebSockets server registered on port 8000</p>
                    <p class="text-slate-500">[2026-06-05 16:15:20] FAISS: Vector index successfully loaded (10 items)</p>
                </div>
            </div>
        </div>

    </div>

</div>
