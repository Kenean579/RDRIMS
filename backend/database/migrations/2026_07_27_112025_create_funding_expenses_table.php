<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_id')->index();
            $table->unsignedBigInteger('budget_category_id')->index();
            $table->unsignedBigInteger('expense_category_id')->index();
            $table->string('reference_number', 100);
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->dateTime('expense_date');
            $table->unsignedBigInteger('submitted_by')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_expenses');
    }
};
