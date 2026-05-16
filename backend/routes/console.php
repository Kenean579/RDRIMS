<?php

use App\Jobs\CheckLicenseExpiryJob;
use App\Jobs\CheckMoUExpiryJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(CheckMoUExpiryJob::class)->dailyAt('08:00');
Schedule::job(CheckLicenseExpiryJob::class)->dailyAt('08:30');
