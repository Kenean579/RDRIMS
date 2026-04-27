<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title', 255);
            $table->text('abstract')->nullable();
            $table->text('keywords')->nullable();
            $table->string('journal', 255);
            $table->string('doi', 255)->nullable();
            $table->string('scholar_url', 255)->nullable();
            $table->date('publication_date');
            $table->integer('citation_count')->default(0);
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamps();

            $table->index('project_id');
            $table->index('doi');
            $table->index('publication_date');
            $table->index('citation_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
