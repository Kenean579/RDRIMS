<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title', 255);
            $table->text('inventors');
            $table->date('filing_date');
            $table->string('patent_number', 100);
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('patent_statuses');
            $table->timestamps();

            $table->index('project_id');
            $table->index('status_id');
            $table->index('patent_number');
            $table->index('filing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patents');
    }
};
