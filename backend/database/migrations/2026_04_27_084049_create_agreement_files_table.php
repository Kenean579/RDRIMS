<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('parent_type_id');
            $table->foreign('parent_type_id')->references('id')->on('agreement_types')->restrictOnDelete();
            $table->unsignedBigInteger('parent_id');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['parent_type_id', 'parent_id', 'file_id']);
            $table->index('file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_files');
    }
};
