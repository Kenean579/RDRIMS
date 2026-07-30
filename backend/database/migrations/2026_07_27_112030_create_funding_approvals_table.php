<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_id')->index();
            $table->string('action', 50);
            $table->unsignedBigInteger('approved_by')->index();
            $table->dateTime('approved_at');
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_approvals');
    }
};
