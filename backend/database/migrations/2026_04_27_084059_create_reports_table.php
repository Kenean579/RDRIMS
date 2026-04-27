<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('file_path', 255);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('generated_at');
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->index('generated_by');
            $table->index('generated_at');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
