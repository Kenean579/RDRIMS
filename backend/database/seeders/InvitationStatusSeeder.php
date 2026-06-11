<?php

namespace Database\Seeders;

use App\Models\InvitationStatus;
use Illuminate\Database\Seeder;

class InvitationStatusSeeder extends Seeder
{
    public function run(): void
    {
        InvitationStatus::firstOrCreate(['name' => 'pending']);
        InvitationStatus::firstOrCreate(['name' => 'accepted']);
        InvitationStatus::firstOrCreate(['name' => 'declined']);
    }
}
