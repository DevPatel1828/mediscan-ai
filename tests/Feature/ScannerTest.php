<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prescription;
use App\Models\PrescriptionResult;
use App\Livewire\Scanner;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScannerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);
    }

    /**
     * Test initiating scan deducts 20 credits and creates database record.
     */
    public function test_initiating_scan_deducts_credits_and_dispatches_websocket(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        // create fake file without using GD image generation
        $file = UploadedFile::fake()->create('prescription.png', 100, 'image/png');

        Livewire::test(Scanner::class)
            ->set('prescriptionImage', $file)
            ->set('scanLanguage', 'English')
            ->call('initiateScan')
            ->assertHasNoErrors()
            ->assertSet('scanStatus', 'uploading')
            ->assertSet('progress', 20)
            ->assertDispatched('start-websocket-scan');

        // Check user credits
        $this->assertEquals(4980, $this->user->fresh()->credits);

        // Check prescription row in database
        $this->assertDatabaseHas('prescriptions', [
            'user_id' => $this->user->id,
            'status' => 'processing',
            'credits_spent' => 20,
        ]);
    }

    /**
     * Test scanning requires at least 20 credits.
     */
    public function test_cannot_scan_if_insufficient_credits(): void
    {
        Storage::fake('public');
        $this->user->update(['credits' => 15]);
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('prescription.png', 100, 'image/png');

        Livewire::test(Scanner::class)
            ->set('prescriptionImage', $file)
            ->call('initiateScan')
            ->assertSet('isProcessing', false)
            ->assertSet('prescriptionId', null);

        $this->assertEquals(15, $this->user->fresh()->credits);
    }

    /**
     * Test completing a scan.
     */
    public function test_scan_completed_loads_results(): void
    {
        $this->actingAs($this->user);

        // Create a dummy prescription in database (status completed as updated by python backend)
        $prescription = Prescription::create([
            'user_id' => $this->user->id,
            'image_path' => 'storage/prescriptions/dummy.png',
            'status' => 'completed',
            'credits_spent' => 20,
        ]);

        $mockJson = json_encode([
            'confidence_score' => 90.0,
            'medicines' => [
                ['name' => 'Aspirin', 'composition' => 'ASA', 'dosage' => '75mg', 'frequency' => 'OD', 'duration' => '30 Days']
            ]
        ]);

        PrescriptionResult::create([
            'prescription_id' => $prescription->id,
            'raw_ocr_text' => 'Tab Aspirin 75mg OD',
            'extracted_json' => $mockJson,
            'confidence_score' => 90.0,
            'language' => 'English'
        ]);

        Livewire::test(Scanner::class)
            ->set('prescriptionId', $prescription->id)
            ->call('scanCompleted', $prescription->id)
            ->assertSet('scanStatus', 'completed')
            ->assertSet('progress', 100)
            ->assertSet('ocrResult.confidence_score', 90.0)
            ->assertCount('history', 1);
    }

    /**
     * Test failing a scan updates database record.
     */
    public function test_scan_failed_updates_status(): void
    {
        $this->actingAs($this->user);

        $prescription = Prescription::create([
            'user_id' => $this->user->id,
            'image_path' => 'storage/prescriptions/dummy.png',
            'status' => 'processing',
            'credits_spent' => 20,
        ]);

        Livewire::test(Scanner::class)
            ->set('prescriptionId', $prescription->id)
            ->call('scanFailed', 'OCR service timed out')
            ->assertSet('scanStatus', 'failed')
            ->assertSet('progress', 0);

        $this->assertEquals('failed', $prescription->fresh()->status);
    }
}
