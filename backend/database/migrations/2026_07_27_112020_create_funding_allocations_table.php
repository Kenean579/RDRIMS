<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_id')->index();
            $table->unsignedBigInteger('budget_category_id')->index();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->decimal('revised_amount', 15, 2)->nullable();
            $table->unsignedBigInteger('revision_approved_by')->nullable();
            $table->dateTime('revision_approved_at')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamps();
            $table->unique(['funding_id', 'budget_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_allocations');
    }
};
