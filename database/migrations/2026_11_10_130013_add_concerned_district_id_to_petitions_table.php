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
        Schema::table('petitions', function (Blueprint $table) {
            $table->unsignedBigInteger('concerned_district_id')->nullable()->after('unit_id');
            $table->foreign('concerned_district_id')->references('district_id')->on('districts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropForeign(['concerned_district_id']);
            $table->dropColumn('concerned_district_id');
        });
    }
};
