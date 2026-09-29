<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary roles to prevent FK constraint failures
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super_admin', 'description' => 'Super Administrator'],
            ['id' => 2, 'name' => 'admin', 'description' => 'System Administrator'],
            ['id' => 3, 'name' => 'premium_user', 'description' => 'Premium Tier User'],
            ['id' => 4, 'name' => 'user', 'description' => 'Standard Tier User'],
        ]);
    }

    /**
     * Test a user can register successfully.
     */
    public function test_user_can_register_successfully(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('mobile_number', '+1234567890')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_number' => '+1234567890',
            'credits' => 5000,
        ]);
    }

    /**
     * Test login with credentials works.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('Password123'),
            'credits' => 5000,
        ]);

        Livewire::test(Login::class)
            ->set('loginMode', 'credentials')
            ->set('email', 'jane@example.com')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login fails with wrong credentials.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('Password123'),
            'credits' => 5000,
        ]);

        Livewire::test(Login::class)
            ->set('loginMode', 'credentials')
            ->set('email', 'jane@example.com')
            ->set('password', 'WrongPassword')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
