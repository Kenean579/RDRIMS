<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('sector', 100);
            $table->string('contact_email', 255);
            $table->string('website', 255)->nullable();
            $table->unsignedBigInteger('research_center_id')->nullable()->index();
            $table->foreign('research_center_id')->references('id')->on('research_centers')->nullOnDelete();
            $table->timestamps();

            $table->index(['name', 'sector', 'contact_email', 'research_center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
