<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Make university_id non‑nullable and add foreign key with cascade on delete
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->unsignedBigInteger('university_id')->nullable(false)->change();
            $table->foreign('university_id')
                ->references('id')
                ->on('universities')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->unsignedBigInteger('university_id')->nullable()->change();
            $table->dropForeign(['university_id']);
            $table->foreign('university_id')
                ->references('id')
                ->on('universities')
                ->nullOnDelete();
        });
    }
};
