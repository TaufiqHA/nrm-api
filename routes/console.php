<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Proses unggahan musik di latar belakang. Jika cron `schedule:run` aktif,
// antrean tetap berjalan walau tidak ada worker permanen.
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=900')
    ->everyMinute()
    ->withoutOverlapping();
