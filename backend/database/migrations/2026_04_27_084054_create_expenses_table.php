<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('budget_category', ['personnel', 'equipment', 'travel', 'other'])->nullable();
            $table->text('description');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('approved_by');
            $table->index('approved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
