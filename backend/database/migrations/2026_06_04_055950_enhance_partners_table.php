<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'type_id')) {
                $table->unsignedTinyInteger('type_id')->nullable()->change();
            } else {
                $table->unsignedTinyInteger('type_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('partners', 'country')) {
                $table->string('country', 100)->nullable()->after('website');
            }

            if (!Schema::hasColumn('partners', 'description')) {
                $table->text('description')->nullable()->after('country');
            }
            
            if (Schema::hasColumn('partners', 'website')) {
                $table->renameColumn('website', 'website_url');
            }
        });

        Schema::table('partners', function (Blueprint $table) {
            // Add foreign key separately
            $table->foreign('type_id')->references('id')->on('agreement_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id', 'country', 'description']);
            if (Schema::hasColumn('partners', 'website_url')) {
                $table->renameColumn('website_url', 'website');
            }
        });
    }
};
