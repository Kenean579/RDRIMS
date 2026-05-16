<?php

namespace Database\Seeders;

use App\Models\InvitationStatus;
use Illuminate\Database\Seeder;

class InvitationStatusSeeder extends Seeder
{
    public function run(): void
    {
        InvitationStatus::create(['name' => 'pending']);
        InvitationStatus::create(['name' => 'accepted']);
        InvitationStatus::create(['name' => 'declined']);
    }
}