<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patent_id')->constrained('patents')->cascadeOnDelete();
            $table->string('company_name', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('royalty_rate', 5, 2);
            $table->timestamps();

            $table->index('patent_id');
            $table->index('company_name');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
