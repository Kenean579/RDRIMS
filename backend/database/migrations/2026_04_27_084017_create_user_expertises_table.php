<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_expertises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expertise_id')->constrained('expertises')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'expertise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_expertises');
    }
};
