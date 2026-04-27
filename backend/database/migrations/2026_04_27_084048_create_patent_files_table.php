<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patent_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patent_id')->constrained('patents')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['patent_id', 'file_id']);
            $table->index('file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patent_files');
    }
};
