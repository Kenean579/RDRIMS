<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->text('description');
            $table->integer('max_score')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_criteria');
    }
};
