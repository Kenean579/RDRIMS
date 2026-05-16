<?php

namespace App\Jobs;

use App\Models\License;
use App\Models\User;
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
        $expiringSoon = License::whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $license) {
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'research_admin']))->get();

            foreach ($admins as $admin) {
                $admin->notifications()->create([
                    'type' => 'license_expiring',
                    'message' => "License for {$license->patent->title} ({$license->company_name}) expires on {$license->end_date->format('Y-m-d')}.",
                    'created_at' => now(),
                ]);
            }
        }
    }
}
