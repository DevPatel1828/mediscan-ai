<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- LEFT SIDE: Search Box & Result List (Col span 1) -->
    <div class="space-y-6 lg:col-span-1">
        
        <!-- Search Input Card -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">Explore Medicines</h3>
            
            <form wire:submit.prevent="search" class="space-y-4">
                <div>
                    <input type="text" wire:model.defer="searchQuery" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm" placeholder="Type name, generic name, or composition...">
                    <span class="text-[10px] text-slate-400 mt-2 block">Each search consumes 5 credits</span>
                </div>
                <button type="submit" class="w-full py-3 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-medium shadow-lg shadow-brand-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <span>🔍</span> Search Database
                </button>
            </form>
        </div>

        <!-- Search Results List -->
        @if(count($searchResults) > 0)
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <h3 class="font-display font-bold text-sm text-slate-900 dark:text-white mb-3">Matching Results</h3>
                <ul class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    @foreach($searchResults as $med)
                        <li>
                            <button wire:click="selectMedicine({{ $med->id }})" class="w-full text-left p-3 rounded-xl border border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-all flex items-center justify-between {{ $selectedMed && $selectedMed->id === $med->id ? 'border-brand-primary bg-brand-primary/5' : '' }}">
                                <div class="min-w-0 flex-1 pr-2">
                                    <p class="text-sm font-bold text-slate-850 dark:text-white truncate">{{ $med->medicine_name }}</p>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $med->generic_name }}</span>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium whitespace-nowrap">
                                    {{ $med->category }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    <!-- RIGHT SIDE: Detailed Medicine Profile (Col span 2) -->
    <div class="lg:col-span-2">
        
        @if($selectedMed)
            <!-- ═══════════ DETAILED PROFILE CARD ═══════════ -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm space-y-6">
                
                <!-- Title & Bookmark Bar -->
                <div class="flex items-start justify-between border-b border-slate-200/50 dark:border-slate-800/50 pb-5">
                    <div>
                        <h2 class="font-display font-extrabold text-2xl text-slate-950 dark:text-white">{{ $selectedMed->medicine_name }}</h2>
                        <p class="text-sm text-slate-400 mt-1 font-medium">{{ $selectedMed->generic_name }} (Composition: {{ $selectedMed->composition }})</p>
                    </div>
                    <button wire:click="toggleBookmark" class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all {{ $isBookmarked ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                        <span>⭐</span> {{ $isBookmarked ? 'Saved' : 'Save Medicine' }}
                    </button>
                </div>

                <!-- Specs Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div class="bg-slate-50/50 dark:bg-slate-950/20 p-3 rounded-xl">
                        <span class="text-slate-400 block mb-0.5">Category</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedMed->category ?? 'N/A' }}</span>
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-950/20 p-3 rounded-xl">
                        <span class="text-slate-400 block mb-0.5">Price (Retail)</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">${{ number_format($selectedMed->price, 2) }}</span>
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-950/20 p-3 rounded-xl">
                        <span class="text-slate-400 block mb-0.5">Manufacturer</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedMed->manufacturer ?? 'N/A' }}</span>
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-950/20 p-3 rounded-xl">
                        <span class="text-slate-400 block mb-0.5">Brand Alias</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedMed->brand_name ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Structured Information Panels -->
                <div class="space-y-4">
                    
                    <!-- Uses -->
                    <div>
                        <h4 class="font-display font-semibold text-sm text-slate-950 dark:text-white mb-1.5 flex items-center gap-1.5">
                            <span>🏥</span> Uses & Indications
                        </h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->uses }}</p>
                    </div>

                    <!-- Dosage -->
                    <div>
                        <h4 class="font-display font-semibold text-sm text-slate-950 dark:text-white mb-1.5 flex items-center gap-1.5">
                            <span>📏</span> Dosage & Administration
                        </h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->dosage }}</p>
                    </div>

                    <!-- Side Effects -->
                    <div>
                        <h4 class="font-display font-semibold text-sm text-slate-950 dark:text-white mb-1.5 flex items-center gap-1.5">
                            <span>⚠️</span> Common Side Effects
                        </h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->side_effects }}</p>
                    </div>

                    <!-- Warnings -->
                    <div class="p-4 rounded-xl bg-red-500/5 border border-red-500/10 text-xs">
                        <h4 class="font-display font-semibold text-sm text-red-500 mb-1.5 flex items-center gap-1.5">
                            <span>🚫</span> Warnings & Contraindications
                        </h4>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->warnings }}</p>
                    </div>

                    <!-- Pregnancy Safety -->
                    <div class="p-4 rounded-xl bg-pink-500/5 border border-pink-500/10 text-xs">
                        <h4 class="font-display font-semibold text-sm text-pink-500 mb-1.5 flex items-center gap-1.5">
                            <span>🤰</span> Pregnancy Safety Rating
                        </h4>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->pregnancy_safety }}</p>
                    </div>

                    <!-- Food & Alcohol Interactions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-orange-500/5 border border-orange-500/10 text-xs">
                            <h4 class="font-display font-semibold text-sm text-orange-500 mb-1.5 flex items-center gap-1.5">
                                🍔 Food Interactions
                            </h4>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->food_interactions }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-purple-500/5 border border-purple-500/10 text-xs">
                            <h4 class="font-display font-semibold text-sm text-purple-500 mb-1.5 flex items-center gap-1.5">
                                🍷 Alcohol Interactions
                            </h4>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->alcohol_interactions }}</p>
                        </div>
                    </div>

                    <!-- Storage -->
                    <div>
                        <h4 class="font-display font-semibold text-sm text-slate-950 dark:text-white mb-1.5 flex items-center gap-1.5">
                            <span>🌡️</span> Storage Conditions
                        </h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{{ $selectedMed->storage_conditions }}</p>
                    </div>

                </div>

            </div>
        @else
            <!-- Placeholder Empty State -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex flex-col items-center justify-center min-h-[50vh] text-center">
                <span class="text-6xl mb-4 animate-bounce-slow">🔍</span>
                <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Medicine Explorer</h3>
                <p class="text-sm text-slate-400 max-w-sm mt-2">Search for a drug on the left to review safety profiles, dosage recommendations, and manufacturer statistics</p>
            </div>
        @endif

    </div>

</div>
