<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['project_id', 'file_id']);
            $table->index('file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};
