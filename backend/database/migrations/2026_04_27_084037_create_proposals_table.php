<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->nullable()->constrained('calls')->nullOnDelete();
            
            $table->unsignedTinyInteger('type_id')->index();
            $table->foreign('type_id')->references('id')->on('proposal_types')->restrictOnDelete();
            
            $table->string('title', 255);
            $table->text('abstract');
            $table->text('objectives');
            $table->text('methodology');
            $table->text('keywords');
            $table->decimal('budget', 12, 2);
            $table->json('budget_allocation')->nullable();
            $table->text('status_change_comment')->nullable();
            
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('proposal_statuses')->restrictOnDelete();
            
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('submitted_at')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            
            $table->unsignedBigInteger('file_id')->nullable()->index();
            $table->unsignedBigInteger('ethics_file_id')->nullable()->index();
            
            $table->unsignedTinyInteger('ethics_approval_status_id')->nullable()->index();
            $table->foreign('ethics_approval_status_id', 'prop_ethics_status_fk')->references('id')->on('ethics_approval_statuses')->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
