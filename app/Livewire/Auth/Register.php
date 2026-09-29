<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $mobile_number = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'mobile_number' => 'nullable|string|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function register()
    {
        $this->validate();

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'mobile_number' => $this->mobile_number ?: null,
                'password' => Hash::make($this->password),
                'credits' => 5000,
            ]);

            // Assign standard user role (role_id = 4)
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => 4
            ]);

            Auth::login($user);
        });

        session()->flash('message', 'Registration successful! Welcome to MediScan AI.');
        return $this->redirect('/dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app', ['title' => 'Register — MediScan AI 2.0']);
    }
}
