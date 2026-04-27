<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_requests', function (Blueprint $table) {
            $table->id();
            $table->string('detectable_type', 50);
            $table->unsignedBigInteger('detectable_id');
            // Index for morph
            $table->index(['detectable_type', 'detectable_id']);
            
            // Files table might be created later
            $table->unsignedBigInteger('file_id')->index();
            
            $table->unsignedTinyInteger('service_id')->index();
            $table->foreign('service_id')->references('id')->on('detection_services')->restrictOnDelete();
            
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('detection_statuses')->restrictOnDelete();
            
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->dateTime('requested_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_requests');
    }
};
