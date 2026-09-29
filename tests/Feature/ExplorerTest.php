<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Medicine;
use App\Livewire\Explorer;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExplorerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);

        $this->medicine = Medicine::create([
            'medicine_name' => 'Aspirin 75mg',
            'generic_name' => 'Acetylsalicylic Acid',
            'composition' => 'Aspirin 75mg',
            'category' => 'Cardiovascular',
        ]);
    }

    /**
     * Test searching consumes 5 credits and finds medicine.
     */
    public function test_searching_consumes_credits_and_finds_medicine(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Explorer::class)
            ->set('searchQuery', 'Aspirin')
            ->call('search')
            ->assertCount('searchResults', 1)
            ->assertSee('Aspirin 75mg');

        $this->assertEquals(4995, $this->user->fresh()->credits);
    }

    /**
     * Test searching with empty query does not consume credits.
     */
    public function test_empty_search_query_does_not_consume_credits(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Explorer::class)
            ->set('searchQuery', '')
            ->call('search')
            ->assertCount('searchResults', 0);

        $this->assertEquals(5000, $this->user->fresh()->credits);
    }

    /**
     * Test user cannot search if they have insufficient credits.
     */
    public function test_cannot_search_if_insufficient_credits(): void
    {
        $this->user->update(['credits' => 2]);
        $this->actingAs($this->user);

        Livewire::test(Explorer::class)
            ->set('searchQuery', 'Aspirin')
            ->call('search')
            ->assertCount('searchResults', 0);

        $this->assertEquals(2, $this->user->fresh()->credits);
    }

    /**
     * Test bookmarking a medicine.
     */
    public function test_can_toggle_medicine_bookmark(): void
    {
        $this->actingAs($this->user);

        // Bookmark
        Livewire::test(Explorer::class)
            ->call('selectMedicine', $this->medicine->id)
            ->assertSet('isBookmarked', false)
            ->call('toggleBookmark')
            ->assertSet('isBookmarked', true);

        $this->assertTrue($this->user->savedMedicines()->where('medicine_id', $this->medicine->id)->exists());

        // Unbookmark
        Livewire::test(Explorer::class)
            ->call('selectMedicine', $this->medicine->id)
            ->assertSet('isBookmarked', true)
            ->call('toggleBookmark')
            ->assertSet('isBookmarked', false);

        $this->assertFalse($this->user->savedMedicines()->where('medicine_id', $this->medicine->id)->exists());
    }
}
