<?php

namespace Database\Seeders;

use App\Models\PublicationType;
use Illuminate\Database\Seeder;

class PublicationTypeSeeder extends Seeder
{
    public function run(): void
    {
        PublicationType::firstOrCreate(['name' => 'journal_article']);
        PublicationType::firstOrCreate(['name' => 'conference_paper']);
        PublicationType::firstOrCreate(['name' => 'book']);
        PublicationType::firstOrCreate(['name' => 'book_chapter']);
        PublicationType::firstOrCreate(['name' => 'thesis']);
        PublicationType::firstOrCreate(['name' => 'technical_report']);
        PublicationType::firstOrCreate(['name' => 'working_paper']);
        PublicationType::firstOrCreate(['name' => 'poster']);
        PublicationType::firstOrCreate(['name' => 'presentation']);
    }
}
