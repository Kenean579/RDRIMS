<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_investigators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable()->index();
            $table->string('institution', 255)->nullable();
            
            $table->unsignedTinyInteger('role_id')->index();
            $table->foreign('role_id')->references('id')->on('investigator_roles')->restrictOnDelete();
            
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('invitation_statuses')->restrictOnDelete();
            
            $table->dateTime('invited_at');
            $table->timestamps();

            // Unique constraints: composite on (proposal_id, user_id) where user_id not null
            // We can add a simple unique constraint and rely on DB handling nulls, or a unique index on proposal_id and email
            $table->unique(['proposal_id', 'user_id']);
            $table->unique(['proposal_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_investigators');
    }
};
