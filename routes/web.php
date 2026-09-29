<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Scanner;
use App\Livewire\Explorer;
use App\Livewire\DrugChecker;
use App\Livewire\AiAssistant;
use App\Livewire\Profile;
use App\Livewire\AdminPanel;
use App\Http\Controllers\ReportController;

// ═══════════ PUBLIC ROUTES ═══════════
Route::get('/', function () {
    return view('welcome');
});

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Logout Handler
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ═══════════ PROTECTED PORTAL ROUTES (Authenticated Users) ═══════════
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/scanner', Scanner::class)->name('scanner');
    Route::get('/explorer', Explorer::class)->name('explorer');
    Route::get('/drug-checker', DrugChecker::class)->name('drug-checker');
    Route::get('/ai-assistant', AiAssistant::class)->name('ai-assistant');
    Route::get('/profile', Profile::class)->name('profile');

    // Report Exports
    Route::get('/reports/pdf/{id}', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/csv/{id}', [ReportController::class, 'csv'])->name('reports.csv');
    Route::get('/reports/xlsx/{id}', [ReportController::class, 'xlsx'])->name('reports.xlsx');

    // ═══════════ ADMIN PORTAL ROUTES (Admins Only) ═══════════
    Route::middleware('admin')->group(function () {
        Route::get('/admin', AdminPanel::class)->name('admin');
    });
});
