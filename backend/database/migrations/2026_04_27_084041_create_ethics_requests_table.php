<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ethics_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->string('generated_pdf_path', 255);
            $table->boolean('submitted_to_irb')->default(false);
            
            $table->unsignedTinyInteger('approval_status_id')->index();
            $table->foreign('approval_status_id')->references('id')->on('ethics_approval_statuses')->restrictOnDelete();
            
            $table->text('comments')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ethics_requests');
    }
};
