<?php

namespace App\Jobs;

use App\Models\MoU;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckMoUExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiringSoon = MoU::whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $moU) {
            // Create in-app notification for admins
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'research_admin']))->get();

            foreach ($admins as $admin) {
                $admin->notifications()->create([
                    'type' => 'mou_expiring',
                    'message' => "MoU with {$moU->partner->name} expires on {$moU->end_date->format('Y-m-d')}.",
                    'created_at' => now(),
                ]);
            }
        }
    }
}
