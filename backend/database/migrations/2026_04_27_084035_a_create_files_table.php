<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 255);
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(false);
            $table->string('mime_type', 100)->nullable();
            $table->string('file_hash', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('uploaded_by');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
