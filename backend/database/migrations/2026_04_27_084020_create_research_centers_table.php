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
            $table->unsignedBigInteger('director_id')->nullable()->index();
            $table->unsignedBigInteger('logo_file_id')->nullable()->index();
            $table->foreignId('university_id')->nullable()->constrained('universities')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->date('established_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_centers');
    }
};
