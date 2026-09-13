<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('log:clear')->weekly();
Schedule::command('queue:prune-batches')->daily();
Schedule::command('queue:prune-failed')->daily();