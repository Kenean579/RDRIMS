<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLicenseExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiringSoon = License::where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $license) {
            // Send notification (implement later)
        }
    }
}
