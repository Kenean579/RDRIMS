<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_id');
            $table->foreign('university_id')->references('id')->on('universities')->cascadeOnDelete();
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->boolean('granted')->default(true);
            $table->timestamps();

            $table->unique(['university_id', 'role_id', 'permission_id'], 'iru_rl_perm_unique');
            $table->index('university_id');
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_role_permissions');
    }
};