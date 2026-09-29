<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CreditTransaction;
use App\Services\CreditManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreditManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user starts with 5000 credits.
     */
    public function test_user_starts_with_5000_credits(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 5000,
        ]);

        $this->assertEquals(5000, $user->credits);
    }

    /**
     * Test hasCredits checks sufficiency.
     */
    public function test_has_credits_checks_sufficiency(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 100,
        ]);

        $this->assertTrue(CreditManager::hasCredits($user, 50));
        $this->assertTrue(CreditManager::hasCredits($user, 100));
        $this->assertFalse(CreditManager::hasCredits($user, 150));
    }

    /**
     * Test credits deduction.
     */
    public function test_credits_deduction_logs_transaction(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 100,
        ]);

        $success = CreditManager::deduct($user, 30, 'scan', 'Scanned standard file');

        $this->assertTrue($success);
        $this->assertEquals(70, $user->fresh()->credits);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'amount' => -30,
            'action_type' => 'scan',
            'description' => 'Scanned standard file',
        ]);
    }

    /**
     * Test credits deduction failure.
     */
    public function test_credits_deduction_fails_if_insufficient(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 10,
        ]);

        $success = CreditManager::deduct($user, 20, 'scan', 'Should fail');

        $this->assertFalse($success);
        $this->assertEquals(10, $user->fresh()->credits);
        $this->assertDatabaseMissing('credit_transactions', [
            'user_id' => $user->id,
            'action_type' => 'scan',
        ]);
    }

    /**
     * Test credits addition.
     */
    public function test_credits_addition_logs_transaction(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 100,
        ]);

        CreditManager::add($user, 500, 'purchase', 'Purchased credit pack');

        $this->assertEquals(600, $user->fresh()->credits);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'amount' => 500,
            'action_type' => 'purchase',
            'description' => 'Purchased credit pack',
        ]);
    }

    /**
     * Test daily reset to 5000 credits.
     */
    public function test_daily_reset_sets_credits_to_5000(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'credits' => 200,
        ]);

        CreditManager::resetDaily($user);

        $this->assertEquals(5000, $user->fresh()->credits);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'amount' => 4800,
            'action_type' => 'daily_reset',
        ]);
    }
}
