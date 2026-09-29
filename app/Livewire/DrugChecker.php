<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use App\Models\MedicineInteraction;

class DrugChecker extends Component
{
    public $searchQuery = '';
    public $searchResults = [];
    public $selectedMeds = [];
    public $interactions = [];

    /**
     * Search for medicines to add to checker
     */
    public function updatedSearchQuery()
    {
        $query = trim($this->searchQuery);
        if (empty($query)) {
            $this->searchResults = [];
            return;
        }

        // Search database, exclude already selected ones
        $selectedIds = collect($this->selectedMeds)->pluck('id')->toArray();
        
        $this->searchResults = Medicine::where(function($q) use ($query) {
                $q->where('medicine_name', 'LIKE', "%{$query}%")
                  ->orWhere('generic_name', 'LIKE', "%{$query}%");
            })
            ->whereNotIn('id', $selectedIds)
            ->take(5)
            ->get();
    }

    /**
     * Add a medicine to checker
     */
    public function addMedicine($id)
    {
        $medicine = Medicine::find($id);
        if ($medicine) {
            $this->selectedMeds[] = $medicine->toArray();
            $this->searchQuery = '';
            $this->searchResults = [];
            
            $this->checkInteractions();
            $this->dispatch('toast', message: "Added {$medicine->medicine_name} to checklist.", type: 'success');
        }
    }

    /**
     * Remove a medicine from checker
     */
    public function removeMedicine($id)
    {
        $this->selectedMeds = collect($this->selectedMeds)
            ->filter(fn($med) => $med['id'] !== $id)
            ->values()
            ->toArray();
            
        $this->checkInteractions();
        $this->dispatch('toast', message: 'Removed medicine from checklist.', type: 'info');
    }

    /**
     * Evaluates interactions between all combinations of selected medicines
     */
    public function checkInteractions()
    {
        $this->interactions = [];
        $count = count($this->selectedMeds);
        
        if ($count < 2) {
            return;
        }

        // Loop through all combinations (pairs) of selected medicines
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $id1 = $this->selectedMeds[$i]['id'];
                $id2 = $this->selectedMeds[$j]['id'];

                // Query interaction in database (either direction)
                $interaction = MedicineInteraction::where(function($q) use ($id1, $id2) {
                        $q->where('medicine_id_1', $id1)->where('medicine_id_2', $id2);
                    })
                    ->orWhere(function($q) use ($id1, $id2) {
                        $q->where('medicine_id_1', $id2)->where('medicine_id_2', $id1);
                    })
                    ->first();

                if ($interaction) {
                    $this->interactions[] = [
                        'med1_name' => $this->selectedMeds[$i]['medicine_name'],
                        'med2_name' => $this->selectedMeds[$j]['medicine_name'],
                        'risk_level' => $interaction->risk_level,
                        'severity' => $interaction->severity,
                        'description' => $interaction->description,
                        'recommended_action' => $interaction->recommended_action
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.drug-checker')
            ->layout('components.layouts.app', ['title' => 'Drug Interaction Checker — MediScan AI 2.0']);
    }
}
