<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('action', 100);
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->text('description');
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('performed_by');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_histories');
    }
};
