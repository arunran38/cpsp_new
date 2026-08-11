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
            $table->unsignedBigInteger('linked_petition_id')->nullable()->after('status')->comment('Self-referencing foreign key for duplicates/linked petitions');
            $table->foreign('linked_petition_id')->references('petition_id')->on('petitions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropForeign(['linked_petition_id']);
            $table->dropColumn('linked_petition_id');
        });
    }
};
