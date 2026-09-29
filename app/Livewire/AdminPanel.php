<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class AdminPanel extends Component
{
    use WithFileUploads;

    public $csvFile;
    
    // Stats
    public $userCount = 0;
    public $scanCount = 0;
    public $totalMeds = 0;
    public $averageConfidence = 0;
    public $creditCostSum = 0;

    // User Management list
    public $usersList = [];
    public $bannedUsers = []; // Simple in-memory mock ban dictionary

    public function mount()
    {
        $this->loadStats();
        $this->loadUsers();
    }

    public function loadStats()
    {
        $this->userCount = User::count();
        $this->scanCount = Prescription::count();
        $this->totalMeds = Medicine::count();
        
        $this->averageConfidence = round(DB::table('prescription_results')->avg('confidence_score') ?? 91.5, 1);
        $this->creditCostSum = abs(DB::table('credit_transactions')->where('amount', '<', 0)->sum('amount'));
    }

    public function loadUsers()
    {
        $this->usersList = User::with('roles')->latest()->get()->toArray();
    }

    /**
     * Reset a specific user's credits to 5000
     */
    public function resetCredits($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['credits' => 5000]);
            
            // Log transaction
            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => 5000 - $user->credits,
                'action_type' => 'admin_reset',
                'description' => 'Credits reset to 5000 by Administrator'
            ]);

            $this->loadStats();
            $this->loadUsers();
            $this->dispatch('toast', message: "Reset credits for {$user->name} successfully!", type: 'success');
        }
    }

    /**
     * Toggle ban state (Mock)
     */
    public function toggleBan($userId)
    {
        $user = User::find($userId);
        if ($user) {
            if (in_array($userId, $this->bannedUsers)) {
                $this->bannedUsers = array_diff($this->bannedUsers, [$userId]);
                $this->dispatch('toast', message: "User {$user->name} unbanned.", type: 'info');
            } else {
                $this->bannedUsers[] = $userId;
                $this->dispatch('toast', message: "User {$user->name} has been banned.", type: 'error');
            }
            $this->loadUsers();
        }
    }

    /**
     * Bulk Medicine Import via CSV
     */
    public function importCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        
        $header = fgetcsv($file); // Read header
        
        // Expected columns mapping (Index based)
        // name, generic, brand, composition, uses, dosage, side_effects, warnings, safety, storage, manufacturer, price, category
        
        $insertedCount = 0;
        
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (count($row) < 1) continue;

                // Map elements safely
                Medicine::create([
                    'medicine_name' => $row[0] ?? 'Unknown Medicine',
                    'generic_name' => $row[1] ?? null,
                    'brand_name' => $row[2] ?? null,
                    'composition' => $row[3] ?? null,
                    'uses' => $row[4] ?? null,
                    'dosage' => $row[5] ?? null,
                    'side_effects' => $row[6] ?? null,
                    'warnings' => $row[7] ?? null,
                    'pregnancy_safety' => $row[8] ?? null,
                    'storage_conditions' => $row[9] ?? null,
                    'manufacturer' => $row[10] ?? null,
                    'price' => isset($row[11]) ? floatval($row[11]) : 0.00,
                    'category' => $row[12] ?? 'General',
                ]);
                $insertedCount++;
            }
            DB::commit();
            
            $this->reset('csvFile');
            $this->loadStats();
            $this->dispatch('toast', message: "Successfully imported {$insertedCount} medicines in bulk!", type: 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', message: "Import failed: " . $e->getMessage(), type: 'error');
        }
        
        fclose($file);
    }

    /**
     * Generates a sample CSV template for the admin to download
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=mediscan_import_template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Medicine Name', 'Generic Name', 'Brand Name', 'Composition', 'Uses', 'Dosage', 
            'Side Effects', 'Warnings', 'Pregnancy Safety', 'Storage Conditions', 'Manufacturer', 'Price', 'Category'
        ];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Add a sample row
            fputcsv($file, [
                'Lisinopril 10mg', 'Lisinopril', 'Prinivil 10', 'Lisinopril 10mg',
                'Treat high blood pressure and heart failure.', '10mg once daily',
                'Dizziness, cough, headache.', 'Do not use if pregnant or history of angioedema.',
                'Category D', 'Store below 30C', 'Merck', '5.40', 'Antihypertensive'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin-panel', [
            'users' => $this->usersList,
        ])->layout('components.layouts.app', ['title' => 'Admin Panel — MediScan AI 2.0']);
    }
}
