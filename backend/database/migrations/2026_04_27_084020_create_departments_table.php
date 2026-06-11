<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->foreignId('faculty_id')->constrained('faculties')->restrictOnDelete();
            $table->unsignedBigInteger('logo_file_id')->nullable()->index();
            $table->timestamps();

            $table->index(['faculty_id', 'name', 'logo_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};