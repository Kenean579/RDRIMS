<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_request_id')->constrained('detection_requests')->cascadeOnDelete();
            $table->float('similarity_score')->nullable();
            $table->float('ai_probability')->nullable();
            
            // file may be created later
            $table->unsignedBigInteger('report_file_id')->nullable()->index();
            
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_results');
    }
};
