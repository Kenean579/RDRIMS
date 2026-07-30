<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
            $table->string('action', 50);
            $table->foreignId('performed_by')->constrained('users');
            $table->text('description');
            $table->json('changes')->nullable();
            $table->timestamps();
            
            $table->index('publication_id');
            $table->index('performed_by');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_histories');
    }
};
