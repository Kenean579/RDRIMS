<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('output_id')->constrained('outputs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('participant_type_id');
            $table->foreign('participant_type_id')->references('id')->on('participant_types')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['output_id', 'user_id', 'participant_type_id']);
            $table->index('user_id');
            $table->index('participant_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_participants');
    }
};
