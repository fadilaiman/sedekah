<?php

use App\Models\MagicLinkToken;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge expired/used magic link tokens daily
Schedule::call(fn () => MagicLinkToken::where('expires_at', '<', now())->delete())
    ->daily();

// Schedule daily stats post at 9 PM Malaysia Time
Schedule::command('daun:post-stats')
    ->dailyAt('21:00')
    ->timezone('Asia/Kuala_Lumpur');
