<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Since users table might be created after depending on migration order, using unsignedBigInteger
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 20);
            $table->string('table_name', 50)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
