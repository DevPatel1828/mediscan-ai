<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Services\CreditManager;

Schedule::call(function () {
    User::chunk(100, function ($users) {
        foreach ($users as $user) {
            CreditManager::resetDaily($user);
        }
    });
})->dailyAt('00:00');
