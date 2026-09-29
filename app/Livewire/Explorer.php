<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use App\Services\CreditManager;
use Illuminate\Support\Facades\DB;

class Explorer extends Component
{
    public $searchQuery = '';
    public $selectedMed = null;
    public $searchResults = [];
    public $isBookmarked = false;

    /**
     * Executes the medicine search query
     */
    public function search()
    {
        $query = trim($this->searchQuery);
        if (empty($query)) {
            $this->searchResults = [];
            return;
        }

        $user = auth()->user();

        // Check credits (cost is 5)
        if (!CreditManager::hasCredits($user, 5)) {
            $this->dispatch('toast', message: 'Insufficient credits! You need 5 credits to search for a medicine.', type: 'error');
            return;
        }

        // Deduct credits
        CreditManager::deduct($user, 5, 'search', "Searched for medicine: {$query}");
        $this->dispatch('credits-updated', credits: $user->fresh()->credits);

        // Find matches in MySQL database
        $this->searchResults = Medicine::where('medicine_name', 'LIKE', "%{$query}%")
            ->orWhere('generic_name', 'LIKE', "%{$query}%")
            ->orWhere('brand_name', 'LIKE', "%{$query}%")
            ->orWhere('composition', 'LIKE', "%{$query}%")
            ->take(10)
            ->get();

        $this->dispatch('toast', message: 'Search completed. 5 credits deducted.', type: 'success');
    }

    /**
     * View detailed information for a medicine
     */
    public function selectMedicine($id)
    {
        $this->selectedMed = Medicine::find($id);
        if ($this->selectedMed) {
            $this->isBookmarked = auth()->user()
                ->savedMedicines()
                ->where('medicine_id', $id)
                ->exists();
        }
    }

    /**
     * Toggles bookmark state for the selected medicine
     */
    public function toggleBookmark()
    {
        if (!$this->selectedMed) return;

        $user = auth()->user();
        if ($this->isBookmarked) {
            $user->savedMedicines()->detach($this->selectedMed->id);
            $this->isBookmarked = false;
            $this->dispatch('toast', message: 'Removed from saved medicines.', type: 'success');
        } else {
            $user->savedMedicines()->attach($this->selectedMed->id);
            $this->isBookmarked = true;
            $this->dispatch('toast', message: 'Saved to profile successfully!', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.explorer')
            ->layout('components.layouts.app', ['title' => 'Medicine Explorer — MediScan AI 2.0']);
    }
}
