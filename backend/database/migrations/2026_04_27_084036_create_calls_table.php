<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->date('deadline')->index();
            $table->text('thematic_areas');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            // Lookup table reference (no cascade usually for lookups)
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('call_statuses')->restrictOnDelete();
            
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            
            // Files might be created later
            $table->unsignedBigInteger('guideline_file_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
