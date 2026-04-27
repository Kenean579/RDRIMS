<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_research_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('research_center_id')->index();
            // Assuming center_roles table exists with tinyIncrements
            $table->unsignedTinyInteger('center_role_id');
            $table->foreign('center_role_id')->references('id')->on('center_roles')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'research_center_id']);
            $table->index('center_role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_research_centers');
    }
};
