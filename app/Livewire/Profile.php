<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Profile extends Component
{
    public $name = '';
    public $email = '';
    public $mobile_number = '';
    
    // Password updates
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // 2FA variables
    public $isTwoFactorEnabled = false;
    public $show2FASetup = false;
    public $twoFactorSecret = '';
    public $twoFactorInput = '';

    // Sanctum Tokens
    public $tokenName = '';
    public $tokens = [];
    public $newTokenValue = '';

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->mobile_number = $user->mobile_number ?? '';
        $this->isTwoFactorEnabled = !empty($user->two_factor_confirmed_at);
        $this->loadTokens();
    }

    public function loadTokens()
    {
        $this->tokens = auth()->user()->tokens()->latest()->get()->toArray();
    }

    /**
     * Update Profile Details
     */
    public function updateProfile()
    {
        $user = auth()->user();
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_number' => 'nullable|string|unique:users,mobile_number,' . $user->id,
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number ?: null,
        ]);

        $this->dispatch('toast', message: 'Profile updated successfully!', type: 'success');
    }

    /**
     * Update User Password
     */
    public function updatePassword()
    {
        $user = auth()->user();
        
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('toast', message: 'Password updated successfully!', type: 'success');
    }

    /**
     * Start 2FA setup process
     */
    public function initiate2FA()
    {
        // Generate a standard base32 simulated secret key
        $this->twoFactorSecret = 'MEDISCAN' . strtoupper(Str::random(10));
        $this->show2FASetup = true;
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirm2FA()
    {
        $this->validate([
            'twoFactorInput' => 'required|digits:6'
        ]);

        // Accept '123456' as valid test validation code
        if ($this->twoFactorInput === '123456') {
            $user = auth()->user();
            $user->update([
                'two_factor_secret' => $this->twoFactorSecret,
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => json_encode([Str::random(10), Str::random(10), Str::random(10)])
            ]);

            $this->isTwoFactorEnabled = true;
            $this->show2FASetup = false;
            $this->twoFactorInput = '';
            $this->dispatch('toast', message: 'Two-Factor Authentication activated!', type: 'success');
        } else {
            $this->addError('twoFactorInput', 'Invalid verification code. Enter "123456" to verify.');
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2FA()
    {
        $user = auth()->user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null
        ]);

        $this->isTwoFactorEnabled = false;
        $this->show2FASetup = false;
        $this->dispatch('toast', message: 'Two-Factor Authentication disabled.', type: 'info');
    }

    /**
     * Generate Sanctum Personal Access Token
     */
    public function generateToken()
    {
        $this->validate([
            'tokenName' => 'required|string|max:255'
        ]);

        $token = auth()->user()->createToken($this->tokenName);
        $this->newTokenValue = $token->plainTextToken;
        $this->tokenName = '';
        
        $this->loadTokens();
        $this->dispatch('toast', message: 'API Token generated successfully!', type: 'success');
    }

    /**
     * Revoke Sanctum Token
     */
    public function revokeToken($tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();
        $this->loadTokens();
        $this->dispatch('toast', message: 'API Token revoked.', type: 'info');
    }

    public function render()
    {
        return view('livewire.profile')
            ->layout('components.layouts.app', ['title' => 'Profile Settings — MediScan AI 2.0']);
    }
}
