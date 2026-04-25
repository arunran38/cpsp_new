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
            $table->softDeletes();
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('uploads', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('petition_forwardings', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('decisions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('petition_forwardings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
