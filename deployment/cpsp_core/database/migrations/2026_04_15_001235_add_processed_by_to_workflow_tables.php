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
        Schema::table('petition_forwardings', function (Blueprint $table) {
            $table->unsignedBigInteger('processed_by_user_id')->nullable()->after('to_unit_id');
            $table->foreign('processed_by_user_id')->references('user_id')->on('users')->nullOnDelete();
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->unsignedBigInteger('processed_by_user_id')->nullable()->after('decided_by_seat_id');
            $table->foreign('processed_by_user_id')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petition_forwardings', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropColumn('processed_by_user_id');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropColumn('processed_by_user_id');
        });
    }
};
