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
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('pen_number')->change();
            $table->unsignedBigInteger('department_id')->nullable()->after('designation_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('deleted_at')->change();
            $table->unsignedBigInteger('department_id')->nullable()->after('designation_id')->change();
        });
    }
};
