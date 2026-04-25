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
        Schema::table('uploads', function (Blueprint $table) {
            // Since we are using SQLite in local dev usually, and change() can be tricky with FKs,
            // we will drop and re-add if needed, but let's try nullable() first.
            // Note: In Laravel 11+, sqlite handles many changes natively.
            $table->unsignedBigInteger('petition_id')->nullable()->change();
            $table->unsignedBigInteger('uploadable_id')->nullable()->change();
            $table->string('uploadable_type')->nullable()->change();
            $table->unsignedBigInteger('uploaded_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('petition_id')->nullable(false)->change();
            $table->unsignedBigInteger('uploadable_id')->nullable(false)->change();
            $table->string('uploadable_type')->nullable(false)->change();
            $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
        });
    }
};
