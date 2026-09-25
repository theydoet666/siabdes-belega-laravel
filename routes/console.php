<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler Retensi Foto Kehadiran Harian (UU No. 27/2022 PDP)
Schedule::command('retensi:bersihkan-foto')->daily()->at('01:00');
