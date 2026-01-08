<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-sync invoices from E-conomic API
// Runs every hour to keep invoice data up-to-date
Schedule::command('invoices:sync')->hourly()->withoutOverlapping();
