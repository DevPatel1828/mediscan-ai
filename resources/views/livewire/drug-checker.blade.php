<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- LEFT SIDE: Selected Medicines list & Selector (Col span 1) -->
    <div class="space-y-6 lg:col-span-1">
        
        <!-- Search Medicine Card -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm relative">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-2">Select Medicines</h3>
            <p class="text-xs text-slate-400 mb-4">Add two or more medicines to check for potential drug interactions.</p>
            
            <div class="relative">
                <input type="text" wire:model.live="searchQuery" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm" placeholder="Type medicine name (e.g. Aspirin, Crocin, Warfarin)...">
                
                <!-- Search Results Overlay -->
                @if(count($searchResults) > 0)
                    <div class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-30 max-h-60 overflow-y-auto">
                        <ul class="p-1 divide-y divide-slate-100 dark:divide-slate-800/30">
                            @foreach($searchResults as $med)
                                <li>
                                    <button type="button" wire:click="addMedicine({{ $med->id }})" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all text-xs font-semibold text-slate-800 dark:text-slate-200 flex items-center justify-between">
                                        <span>{{ $med->medicine_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $med->category }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Checklist Card -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-sm text-slate-900 dark:text-white mb-4">Checked List</h3>
            
            <div class="flex flex-wrap gap-2">
                @forelse($selectedMeds as $med)
                    <div class="px-3.5 py-2 rounded-xl bg-brand-primary/10 text-brand-primary border border-brand-primary/20 text-xs font-bold flex items-center gap-2 hover:scale-[1.02] transition-transform">
                        <span>{{ $med['medicine_name'] }}</span>
                        <button type="button" wire:click="removeMedicine({{ $med['id'] }})" class="hover:text-red-500 font-bold">✕</button>
                    </div>
                @empty
                    <div class="w-full text-center py-8 text-slate-400 text-xs">
                        No medicines added to checklist yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- RIGHT SIDE: Interactions Report (Col span 2) -->
    <div class="lg:col-span-2">
        
        @if(count($selectedMeds) >= 2)
            <!-- ═══════════ INTERACTION REPORT VIEW ═══════════ -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-200/50 dark:border-slate-800/50 pb-5">
                    <h2 class="font-display font-extrabold text-xl text-slate-950 dark:text-white">Interaction Analysis Report</h2>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium">
                        {{ count($selectedMeds) }} Medications Selected
                    </span>
                </div>

                @forelse($interactions as $inter)
                    <!-- Interaction Card -->
                    <div class="p-6 rounded-2xl border transition-all duration-300 {{
                        $inter['risk_level'] === 'critical' 
                        ? 'bg-red-500/10 border-red-500/20 text-red-700 dark:text-red-400' 
                        : ($inter['risk_level'] === 'high' 
                            ? 'bg-orange-500/10 border-orange-500/20 text-orange-700 dark:text-orange-400' 
                            : ($inter['risk_level'] === 'medium' ? 'bg-yellow-500/10 border-yellow-500/20 text-yellow-700 dark:text-yellow-400' : 'bg-slate-500/10 border-slate-500/20 text-slate-700 dark:text-slate-400'))
                    }}">
                        <div class="flex items-center justify-between border-b border-current/20 pb-3 mb-3">
                            <h4 class="font-bold text-sm uppercase tracking-wider flex items-center gap-1.5">
                                <span>⚠️</span> {{ $inter['risk_level'] }} interaction
                            </h4>
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-current/10 uppercase">
                                {{ $inter['severity'] }}
                            </span>
                        </div>
                        
                        <p class="font-bold text-slate-900 dark:text-white mb-2 text-sm">
                            {{ $inter['med1_name'] }} <span class="font-medium text-slate-500">interacts with</span> {{ $inter['med2_name'] }}
                        </p>
                        
                        <p class="text-xs leading-relaxed font-medium mb-3 opacity-90">
                            <span class="font-semibold block mb-0.5 opacity-60">Risk Description:</span>
                            {{ $inter['description'] }}
                        </p>

                        <p class="text-xs leading-relaxed font-medium p-3 rounded-xl bg-white/40 dark:bg-slate-950/20">
                            <span class="font-semibold block mb-0.5 opacity-60 text-slate-800 dark:text-slate-200">Recommended Action:</span>
                            {{ $inter['recommended_action'] }}
                        </p>
                    </div>
                @empty
                    <!-- No Interactions Card -->
                    <div class="p-8 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-500 text-center flex flex-col items-center justify-center py-12">
                        <span class="text-5xl mb-4">🛡️</span>
                        <h4 class="font-display font-bold text-lg">No Known Interactions Found</h4>
                        <p class="text-xs text-slate-400 mt-2 max-w-sm">No documented drug-drug interactions exist between these selected medications in our database. However, always consult your pharmacist or physician.</p>
                    </div>
                @endforelse

            </div>
        @else
            <!-- Placeholder Empty State -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex flex-col items-center justify-center min-h-[50vh] text-center">
                <span class="text-6xl mb-4 animate-bounce-slow">🛡️</span>
                <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Drug Interaction Checker</h3>
                <p class="text-sm text-slate-400 max-w-sm mt-2">Add two or more medicines (e.g. Aspirin + Warfarin or Crocin + Combiflam) on the left to evaluate clinical interactions</p>
            </div>
        @endif

    </div>

</div>
