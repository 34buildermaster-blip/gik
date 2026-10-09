<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('project-events:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('operations:send-daily-summary')
    ->weekdays()
    ->dailyAt('07:30')
    ->timezone(config('app.display_timezone'))
    ->withoutOverlapping();

Schedule::command('system:backup-database')
    ->dailyAt('02:30')
    ->timezone(config('app.display_timezone'))
    ->withoutOverlapping();

Schedule::command('system:health-check --notify')
    ->dailyAt('03:15')
    ->timezone(config('app.display_timezone'))
    ->withoutOverlapping();
