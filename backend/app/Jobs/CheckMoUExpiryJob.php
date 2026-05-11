<?php

namespace App\Jobs;

use App\Models\MoU;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckMoUExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiringSoon = MoU::where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $moU) {
            // Send notification (implement later when notifications are ready)
            // Notification::send($moU->partner->users, new MoUExpiryNotification($moU));
        }
    }
}
