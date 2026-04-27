<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mo_u_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index('partner_id');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mo_u_s');
    }
};
