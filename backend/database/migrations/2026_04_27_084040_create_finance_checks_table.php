<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('finance_check_statuses')->restrictOnDelete();
            
            $table->text('comments')->nullable();
            $table->dateTime('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_checks');
    }
};
