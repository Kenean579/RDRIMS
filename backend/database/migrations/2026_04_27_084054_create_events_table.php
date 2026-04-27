<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('venue', 255);
            $table->text('description');
            $table->integer('capacity')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->foreignId('image_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('venue');
            $table->index('image_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
