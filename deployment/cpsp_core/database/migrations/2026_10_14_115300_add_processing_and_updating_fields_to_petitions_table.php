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
            if (!Schema::hasColumn('petitions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('cpsp_opened_by_seat_id');
            }
            if (!Schema::hasColumn('petitions', 'processed_by_user_id')) {
                $table->unsignedBigInteger('processed_by_user_id')->nullable()->after('processed_at');
                $table->foreign('processed_by_user_id')->references('user_id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('petitions', 'processed_by_seat_id')) {
                $table->unsignedBigInteger('processed_by_seat_id')->nullable()->after('processed_by_user_id');
                $table->foreign('processed_by_seat_id')->references('seat_id')->on('seats')->onDelete('set null');
            }
            if (!Schema::hasColumn('petitions', 'updated_by_user_id')) {
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->after('processed_by_seat_id');
                $table->foreign('updated_by_user_id')->references('user_id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('petitions', 'updated_by_seat_id')) {
                $table->unsignedBigInteger('updated_by_seat_id')->nullable()->after('updated_by_user_id');
                $table->foreign('updated_by_seat_id')->references('seat_id')->on('seats')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'updated_by_seat_id')) {
                $table->dropForeign(['updated_by_seat_id']);
                $table->dropColumn('updated_by_seat_id');
            }
            if (Schema::hasColumn('petitions', 'updated_by_user_id')) {
                $table->dropForeign(['updated_by_user_id']);
                $table->dropColumn('updated_by_user_id');
            }
            if (Schema::hasColumn('petitions', 'processed_by_seat_id')) {
                $table->dropForeign(['processed_by_seat_id']);
                $table->dropColumn('processed_by_seat_id');
            }
            if (Schema::hasColumn('petitions', 'processed_by_user_id')) {
                $table->dropForeign(['processed_by_user_id']);
                $table->dropColumn('processed_by_user_id');
            }
            if (Schema::hasColumn('petitions', 'processed_at')) {
                $table->dropColumn('processed_at');
            }
        });
    }
};
