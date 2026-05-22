<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_access_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->string('label', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('publication_statuses', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->string('label', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title', 255);
            $table->text('abstract')->nullable();
            $table->text('keywords')->nullable();
            $table->string('journal_name', 255);
            $table->string('doi', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->date('publication_date');
            $table->string('volume', 50)->nullable();
            $table->string('issue', 50)->nullable();
            $table->string('pages', 50)->nullable();
            
            $table->unsignedTinyInteger('access_type_id')->default(1)->index();
            $table->foreign('access_type_id')->references('id')->on('publication_access_types')->restrictOnDelete();

            $table->unsignedTinyInteger('status_id')->default(2)->index(); // published
            $table->foreign('status_id')->references('id')->on('publication_statuses')->restrictOnDelete();

            $table->unsignedBigInteger('cover_image_id')->nullable()->index();
            $table->foreign('cover_image_id')->references('id')->on('files')->nullOnDelete();
            $table->timestamps();

            $table->index('project_id');
            $table->index('doi');
            $table->index('publication_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
        Schema::dropIfExists('publication_statuses');
        Schema::dropIfExists('publication_access_types');
    }
};
