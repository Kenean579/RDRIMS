<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('code', 50)->unique();
            // Using unsignedBigInteger for files & users to avoid migration order issues, 
            // but for parents we can use constrained as those tables are earlier.
            $table->unsignedBigInteger('director_id')->nullable()->index();
            $table->unsignedBigInteger('logo_file_id')->nullable()->index();
            $table->foreignId('parent_university_id')->nullable()->constrained('universities')->cascadeOnDelete();
            $table->foreignId('parent_campus_id')->nullable()->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('parent_faculty_id')->nullable()->constrained('faculties')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_centers');
    }
};
