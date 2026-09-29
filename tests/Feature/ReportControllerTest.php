<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prescription;
use App\Models\PrescriptionResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $otherUser;
    private $prescription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);

        $this->otherUser = User::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);

        $this->prescription = Prescription::create([
            'user_id' => $this->user->id,
            'image_path' => 'storage/prescriptions/dummy.png',
            'status' => 'completed',
            'credits_spent' => 20,
        ]);

        PrescriptionResult::create([
            'prescription_id' => $this->prescription->id,
            'raw_ocr_text' => "Tab Azithral 500 OD x 5 Days",
            'extracted_json' => json_encode([
                'confidence_score' => 95.0,
                'medicines' => [
                    [
                        'name' => 'Azithral',
                        'composition' => 'Azithromycin',
                        'dosage' => '500mg',
                        'frequency' => 'OD',
                        'duration' => '5 Days',
                        'instructions' => 'Take one daily'
                    ]
                ],
                'doctor_instructions' => 'Take after meals'
            ]),
            'confidence_score' => 95.0,
            'language' => 'English'
        ]);
    }

    /**
     * Test authorized PDF export.
     */
    public function test_authorized_user_can_export_pdf(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.pdf', $this->prescription->id));

        $response->assertStatus(200);
        $response->assertViewIs('reports.prescription-pdf');
        $response->assertSee('Azithral');
        
        $this->assertEquals(4990, $this->user->fresh()->credits);
    }

    /**
     * Test unauthorized PDF export.
     */
    public function test_unauthorized_user_cannot_export_pdf(): void
    {
        $this->actingAs($this->otherUser);

        $response = $this->get(route('reports.pdf', $this->prescription->id));

        $response->assertStatus(403);
        $this->assertEquals(5000, $this->otherUser->fresh()->credits);
    }

    /**
     * Test authorized CSV export.
     */
    public function test_authorized_user_can_export_csv(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.csv', $this->prescription->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $this->assertEquals(4990, $this->user->fresh()->credits);
    }

    /**
     * Test authorized XLS export.
     */
    public function test_authorized_user_can_export_xlsx(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.xlsx', $this->prescription->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        
        $this->assertEquals(4990, $this->user->fresh()->credits);
    }
}
