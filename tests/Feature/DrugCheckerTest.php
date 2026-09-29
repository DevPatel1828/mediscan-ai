<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineInteraction;
use App\Livewire\DrugChecker;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DrugCheckerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $aspirin;
    private $warfarin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard user
        $this->user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);

        // Create medicines
        $this->aspirin = Medicine::create([
            'medicine_name' => 'Aspirin 75mg',
            'generic_name' => 'Acetylsalicylic Acid',
            'composition' => 'Aspirin 75mg',
            'category' => 'Cardiovascular',
        ]);

        $this->warfarin = Medicine::create([
            'medicine_name' => 'Warfarin 5mg',
            'generic_name' => 'Warfarin Sodium',
            'composition' => 'Warfarin Sodium 5mg',
            'category' => 'Anticoagulant',
        ]);

        // Create interaction record
        MedicineInteraction::create([
            'medicine_id_1' => $this->aspirin->id,
            'medicine_id_2' => $this->warfarin->id,
            'risk_level' => 'critical',
            'severity' => 'Major Bleeding Risk',
            'description' => 'Concomitant use increases bleeding risk.',
            'recommended_action' => 'Avoid combination.',
        ]);
    }

    /**
     * Test searching for medicines.
     */
    public function test_can_search_medicines_in_checker(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DrugChecker::class)
            ->set('searchQuery', 'Aspirin')
            ->assertCount('searchResults', 1)
            ->assertSee('Aspirin 75mg');
    }

    /**
     * Test adding medicine to checklist.
     */
    public function test_can_add_medicine_to_checklist(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DrugChecker::class)
            ->call('addMedicine', $this->aspirin->id)
            ->assertCount('selectedMeds', 1)
            ->assertSet('selectedMeds.0.id', $this->aspirin->id)
            ->assertSet('interactions', []);
    }

    /**
     * Test evaluating interaction between two medicines.
     */
    public function test_evaluates_interaction_between_added_medicines(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DrugChecker::class)
            ->call('addMedicine', $this->aspirin->id)
            ->call('addMedicine', $this->warfarin->id)
            ->assertCount('selectedMeds', 2)
            ->assertCount('interactions', 1)
            ->assertSet('interactions.0.risk_level', 'critical')
            ->assertSet('interactions.0.severity', 'Major Bleeding Risk');
    }

    /**
     * Test removing medicine updates interactions list.
     */
    public function test_removing_medicine_clears_interactions(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DrugChecker::class)
            ->call('addMedicine', $this->aspirin->id)
            ->call('addMedicine', $this->warfarin->id)
            ->assertCount('interactions', 1)
            ->call('removeMedicine', $this->warfarin->id)
            ->assertCount('selectedMeds', 1)
            ->assertCount('interactions', 0);
    }
}
