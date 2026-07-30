<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fundings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_id')->index();
            $table->unsignedBigInteger('funding_source_id')->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('proposal_id')->nullable()->index();
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->string('reference_number', 100)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_internal')->default(false);
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fundings');
    }
};
