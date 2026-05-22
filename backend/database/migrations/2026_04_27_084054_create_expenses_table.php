<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_statuses', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->string('label', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->string('label', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 255);
            $table->decimal('amount', 12, 2);
            
            $table->unsignedTinyInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('expense_categories')->restrictOnDelete();

            $table->date('expense_date')->index();

            $table->unsignedTinyInteger('status_id')->default(1)->index();
            $table->foreign('status_id')->references('id')->on('expense_statuses')->restrictOnDelete();

            $table->text('description')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('evidence_file_id')->nullable()->index();
            $table->foreign('evidence_file_id')->references('id')->on('files')->nullOnDelete();
            $table->timestamps();

            $table->index('project_id');
            $table->index('approved_by');
            $table->index('approved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('expense_statuses');
    }
};
