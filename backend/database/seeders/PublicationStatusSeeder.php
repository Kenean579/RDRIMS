<?php

namespace Database\Seeders;

use App\Models\PublicationStatus;
use Illuminate\Database\Seeder;

class PublicationStatusSeeder extends Seeder
{
    public function run(): void
    {
        PublicationStatus::firstOrCreate(['name' => 'draft']);
        PublicationStatus::firstOrCreate(['name' => 'submitted']);
        PublicationStatus::firstOrCreate(['name' => 'under_review']);
        PublicationStatus::firstOrCreate(['name' => 'accepted']);
        PublicationStatus::firstOrCreate(['name' => 'rejected']);
        PublicationStatus::firstOrCreate(['name' => 'published']);
    }
}
