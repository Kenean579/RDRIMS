<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('external_author_name', 255)->nullable();
            $table->string('external_institution', 255)->nullable();
            $table->integer('author_order');
            $table->timestamps();

            $table->unique(['publication_id', 'user_id']);
            $table->index('user_id');
            $table->index('author_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_authors');
    }
};
