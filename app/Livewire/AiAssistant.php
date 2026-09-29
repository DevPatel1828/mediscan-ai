<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatHistory;
use App\Services\CreditManager;

class AiAssistant extends Component
{
    public $chatHistory = [];

    public function mount()
    {
        $this->loadChatHistory();
    }

    public function loadChatHistory()
    {
        $this->chatHistory = ChatHistory::where('user_id', auth()->id())
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Triggered by Livewire after a WebSocket message completes and credits are deducted in DB
     */
    public function refreshCreditsAndHistory()
    {
        $this->loadChatHistory();
        $this->dispatch('credits-updated', credits: auth()->user()->fresh()->credits);
    }

    public function render()
    {
        return view('livewire.ai-assistant')
            ->layout('components.layouts.app', ['title' => 'AI Healthcare Assistant — MediScan AI 2.0']);
    }
}
