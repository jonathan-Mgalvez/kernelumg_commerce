<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Mantenimiento diario (requiere `php artisan schedule:work` o una tarea programada de Windows)
Schedule::command('ferrotek:purgar')->dailyAt('02:00');
