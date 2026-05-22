<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_statuses', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->string('label', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location', 255);
            $table->text('description');
            $table->integer('max_participants')->nullable();
            
            $table->unsignedTinyInteger('status_id')->default(1)->index();
            $table->foreign('status_id')->references('id')->on('event_statuses')->restrictOnDelete();

            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('registration_deadline')->nullable();
            $table->unsignedBigInteger('banner_id')->nullable()->index();
            $table->foreign('banner_id')->references('id')->on('files')->nullOnDelete();
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_statuses');
    }
};
