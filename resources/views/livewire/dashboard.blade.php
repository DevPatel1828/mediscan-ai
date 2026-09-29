<div>
    <!-- ═══════════ HEADER WIDGETS ═══════════ -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Credits Widget -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between group hover:scale-[1.02] transition-transform duration-300">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Remaining Credits</span>
                <h3 class="font-display font-extrabold text-3xl text-slate-900 dark:text-white mt-1 group-hover:text-brand-primary transition-colors">{{ $remainingCredits }}</h3>
                <p class="text-xs text-slate-400 mt-2">Deducted per AI processing</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-brand-primary/10 text-brand-primary text-2xl flex items-center justify-center border border-brand-primary/20 shadow-inner">
                💎
            </div>
        </div>

        <!-- Scans Widget -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between group hover:scale-[1.02] transition-transform duration-300">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Prescription Scans</span>
                <h3 class="font-display font-extrabold text-3xl text-slate-900 dark:text-white mt-1 group-hover:text-brand-secondary transition-colors">{{ $totalScans }}</h3>
                <p class="text-xs text-slate-400 mt-2">Handwritten & printed reports</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-brand-secondary/10 text-brand-secondary text-2xl flex items-center justify-center border border-brand-secondary/20 shadow-inner">
                📷
            </div>
        </div>

        <!-- Saved Medicines Widget -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between group hover:scale-[1.02] transition-transform duration-300">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saved Medicines</span>
                <h3 class="font-display font-extrabold text-3xl text-slate-900 dark:text-white mt-1 group-hover:text-brand-accent transition-colors">{{ $savedMedsCount }}</h3>
                <p class="text-xs text-slate-400 mt-2">Bookmarked for offline reference</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-brand-accent/10 text-brand-accent text-2xl flex items-center justify-center border border-brand-accent/20 shadow-inner">
                💾
            </div>
        </div>
    </div>

    <!-- ═══════════ MAIN CONTENT GRID ═══════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Left 2 Columns: Scan History and Charts -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Usage Charts -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Platform Usage Metrics</h3>
                    <span class="text-xs text-slate-400">Last 7 Days</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>

            <!-- Recent Prescription Scans -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Recent Prescription Scans</h3>
                    <a href="/scanner" class="text-xs font-semibold text-brand-primary hover:underline">New Scan ➔</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/50 dark:border-slate-800/50 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="pb-3">Prescription</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Confidence</th>
                                <th class="pb-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-sm">
                            @forelse($recentScans as $scan)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                    <td class="py-3.5 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200/50 dark:border-slate-800/50">
                                            <img src="{{ $scan->image_path }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-white">Scan #{{ $scan->id }}</p>
                                            <span class="text-xs text-slate-400">{{ $scan->result->language ?? 'English' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{
                                            $scan->status === 'completed' 
                                            ? 'bg-green-500/10 text-green-500 border border-green-500/20' 
                                            : ($scan->status === 'failed' ? 'bg-red-500/10 text-red-500 border border-red-500/20' : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20')
                                        }}">
                                            {{ ucfirst($scan->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 font-mono font-semibold">
                                        @if($scan->result)
                                            <span class="{{ $scan->result->confidence_score >= 80 ? 'text-green-500' : 'text-yellow-500' }}">
                                                {{ $scan->result->confidence_score }}%
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right text-slate-400 font-medium">
                                        {{ $scan->created_at->format('M d, g:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        No recent prescription scans found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right 1 Column: Credit Activity and Bookmarks -->
        <div class="space-y-8">
            <!-- Saved Medicines Bookmarks -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Saved Medicines</h3>
                    <a href="/explorer" class="text-xs font-semibold text-brand-primary hover:underline">Explore ➔</a>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($savedMedicines as $med)
                        <li class="py-3.5 flex items-center justify-between group hover:translate-x-1 transition-transform">
                            <div>
                                <h4 class="font-semibold text-slate-950 dark:text-white">{{ $med->medicine_name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $med->generic_name }}</p>
                            </div>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium">
                                {{ $med->category }}
                            </span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-slate-400 text-sm">
                            No bookmarked medicines.
                        </li>
                    @endforelse
                </ul>
            </div>

            <!-- Recent Credit Ledger -->
            <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">Credit Statement</h3>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($recentTransactions as $tx)
                        <li class="py-3 flex items-center justify-between text-sm">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ ucfirst($tx->action_type) }}</p>
                                <span class="text-xs text-slate-400">{{ $tx->created_at->format('M d, h:i A') }}</span>
                            </div>
                            <span class="font-mono font-bold {{ $tx->amount < 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}
                            </span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-slate-400 text-sm">
                            No recent credit movements.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>

    <!-- Chart.js and rendering scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            renderUsageChart();
        });

        document.addEventListener('DOMContentLoaded', () => {
            renderUsageChart();
        });

        function renderUsageChart() {
            const ctx = document.getElementById('usageChart');
            if (!ctx) return;
            
            // Check if chart instance already exists to avoid canvas errors
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            const labels = {!! $chartLabels !!};
            const scans = {!! $scanCounts !!};
            const credits = {!! $creditSpents !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Prescriptions Scanned',
                            data: scans,
                            borderColor: '#14B8A6',
                            backgroundColor: 'rgba(20, 184, 166, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Credits Used',
                            data: credits,
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#94A3B8' : '#475569',
                                font: {
                                    family: 'Inter'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? 'rgba(148, 163, 184, 0.05)' : 'rgba(71, 85, 105, 0.05)'
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#64748B' : '#94A3B8',
                                font: {
                                    family: 'Inter'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#64748B' : '#94A3B8',
                                font: {
                                    family: 'Inter'
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</div>
