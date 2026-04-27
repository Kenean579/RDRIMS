<?php

namespace Database\Seeders;

use App\Models\InvitationStatus;
use Illuminate\Database\Seeder;

class InvitationStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'accepted', 'declined'];
        foreach ($statuses as $status) {
            InvitationStatus::updateOrCreate(['name' => $status]);
        }
    }
}
