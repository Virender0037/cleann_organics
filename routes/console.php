<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// NimbusPost documents no webhook, so tracking is polled. Runs only if the server has the Laravel scheduler cron
// (`* * * * * php artisan schedule:run`); without it, admins refresh tracking from the order page.
Schedule::command('nimbuspost:sync-tracking')->hourly()->withoutOverlapping();
