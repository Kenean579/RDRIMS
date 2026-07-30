<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_id')->index();
            $table->string('action', 50);
            $table->unsignedBigInteger('performed_by')->index();
            $table->json('changes')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_histories');
    }
};
