<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->unique(); // 'en', 'am'
            $table->string('label', 50); // 'English', 'Amharic'
            $table->timestamps();
        });

        Schema::create('language_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->unsignedBigInteger('locale_id')->index();
            $table->foreign('locale_id')->references('id')->on('locales')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_preferences');
        Schema::dropIfExists('locales');
    }
};
