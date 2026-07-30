<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publication_authors', function (Blueprint $table) {
            $table->string('contribution_role', 50)->nullable()->after('author_order'); // first_author, corresponding_author, co_author
            $table->boolean('is_corresponding')->default(false)->after('contribution_role');
            
            $table->index('contribution_role');
        });
    }

    public function down(): void
    {
        Schema::table('publication_authors', function (Blueprint $table) {
            $table->dropColumn(['contribution_role', 'is_corresponding']);
        });
    }
};
