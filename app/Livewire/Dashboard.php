<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Prescription;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $remainingCredits = 0;
    public $totalScans = 0;
    public $savedMedsCount = 0;

    public function mount()
    {
        $user = auth()->user();
        $this->remainingCredits = $user->credits;
        $this->totalScans = $user->prescriptions()->count();
        $this->savedMedsCount = $user->savedMedicines()->count();
    }

    public function render()
    {
        $user = auth()->user();

        // 1. Recent scan history
        $recentScans = Prescription::with('result')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 2. Recent credit transactions
        $recentTransactions = CreditTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 3. Recently saved medicines
        $savedMedicines = $user->savedMedicines()
            ->latest()
            ->take(5)
            ->get();

        // 4. Generate usage statistics for charts (Last 7 Days)
        $chartLabels = [];
        $scanCounts = [];
        $creditSpents = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M d');
            $chartLabels[] = $label;

            $scansToday = Prescription::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->count();
            $scanCounts[] = $scansToday;

            $creditsToday = abs(CreditTransaction::where('user_id', $user->id)
                ->where('amount', '<', 0)
                ->whereDate('created_at', $date)
                ->sum('amount'));
            $creditSpents[] = $creditsToday;
        }

        return view('livewire.dashboard', [
            'recentScans' => $recentScans,
            'recentTransactions' => $recentTransactions,
            'savedMedicines' => $savedMedicines,
            'chartLabels' => json_encode($chartLabels),
            'scanCounts' => json_encode($scanCounts),
            'creditSpents' => json_encode($creditSpents),
        ])->layout('components.layouts.app', ['title' => 'Dashboard — MediScan AI 2.0']);
    }
}
