<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Prescription;
use App\Models\PrescriptionResult;
use App\Services\CreditManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Scanner extends Component
{
    use WithFileUploads;

    public $prescriptionImage;
    public $scanLanguage = 'English';
    
    // UI state
    public $prescriptionId = null;
    public $imagePath = '';
    public $imageUrl = '';
    public $isProcessing = false;
    public $scanStatus = ''; // pending, enhancing, extracting, validating, completed, failed
    public $progress = 0;
    
    // Result
    public $ocrResult = null;
    
    // History
    public $history = [];

    protected $rules = [
        'prescriptionImage' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240', // Max 10MB
    ];

    public function mount()
    {
        $this->loadHistory();
    }

    public function loadHistory()
    {
        $this->history = Prescription::with('result')
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->get();
    }

    /**
     * Start the Scan flow (Laravel side)
     */
    public function initiateScan()
    {
        $this->validate();

        $user = auth()->user();

        // 1. Check Credits (cost is 20)
        if (!CreditManager::hasCredits($user, 20)) {
            $this->dispatch('toast', message: 'Insufficient credits! You need 20 credits to scan a prescription.', type: 'error');
            return;
        }

        // 2. Save file
        $fileName = Str::random(40) . '.' . $this->prescriptionImage->getClientOriginalExtension();
        $storedPath = $this->prescriptionImage->storeAs('prescriptions', $fileName, 'public');
        
        $this->imagePath = 'storage/' . $storedPath;
        $this->imageUrl = asset($this->imagePath);

        // 3. Deduct Credits
        CreditManager::deduct($user, 20, 'scan', "Scanned prescription image: {$fileName}");

        // 4. Create Prescription log
        $prescription = Prescription::create([
            'user_id' => $user->id,
            'image_path' => $this->imagePath,
            'status' => 'processing',
            'credits_spent' => 20
        ]);

        $this->prescriptionId = $prescription->id;
        $this->isProcessing = true;
        $this->scanStatus = 'uploading';
        $this->progress = 20;

        // Dispatch credits updated event to header
        $this->dispatch('credits-updated', credits: auth()->user()->credits);

        // Tell frontend (Alpine) to start WebSocket connection to FastAPI
        $this->dispatch('start-websocket-scan', 
            prescriptionId: $prescription->id,
            imagePath: public_path($this->imagePath), // Send local absolute path to Python
            imageUrl: $this->imageUrl,
            language: $this->scanLanguage
        );
    }

    /**
     * Called by Livewire after WebSocket reports success
     */
    public function scanCompleted($resultId)
    {
        $this->isProcessing = false;
        $this->scanStatus = 'completed';
        $this->progress = 100;
        
        $result = PrescriptionResult::where('prescription_id', $this->prescriptionId)->first();
        if ($result) {
            $this->ocrResult = json_decode($result->extracted_json, true);
        }

        $this->loadHistory();
        $this->dispatch('toast', message: 'Scan processed successfully!', type: 'success');
    }

    /**
     * Called by Livewire after WebSocket reports failure
     */
    public function scanFailed($errorMsg)
    {
        $this->isProcessing = false;
        $this->scanStatus = 'failed';
        $this->progress = 0;
        
        if ($this->prescriptionId) {
            Prescription::find($this->prescriptionId)->update(['status' => 'failed']);
        }

        $this->dispatch('toast', message: 'Scan failed: ' . $errorMsg, type: 'error');
    }

    /**
     * Load an old scan from history
     */
    public function loadScan($id)
    {
        $prescription = Prescription::with('result')->find($id);
        if ($prescription && $prescription->user_id === auth()->id()) {
            $this->prescriptionId = $prescription->id;
            $this->imagePath = $prescription->image_path;
            $this->imageUrl = asset($prescription->image_path);
            
            if ($prescription->result) {
                $this->ocrResult = json_decode($prescription->result->extracted_json, true);
                $this->scanStatus = 'completed';
            } else {
                $this->ocrResult = null;
                $this->scanStatus = $prescription->status;
            }
        }
    }

    public function render()
    {
        return view('livewire.scanner')
            ->layout('components.layouts.app', ['title' => 'Scan Prescription — MediScan AI 2.0']);
    }
}
