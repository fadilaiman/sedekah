<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily stats post at 9 PM Malaysia Time
Schedule::command('daun:post-stats')
    ->dailyAt('21:00')
    ->timezone('Asia/Kuala_Lumpur');
