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
        if (!Schema::hasColumn('petitions', 'cpsp_opened_at')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->timestamp('cpsp_opened_at')->nullable()->after('is_cpsp_processed');
                $table->unsignedBigInteger('cpsp_opened_by_user_id')->nullable()->after('cpsp_opened_at');
                $table->unsignedBigInteger('cpsp_opened_by_seat_id')->nullable()->after('cpsp_opened_by_user_id');

                $table->foreign('cpsp_opened_by_user_id')->references('user_id')->on('users')->onDelete('set null');
                $table->foreign('cpsp_opened_by_seat_id')->references('seat_id')->on('seats')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'cpsp_opened_by_seat_id')) {
                $table->dropForeign(['cpsp_opened_by_seat_id']);
                $table->dropColumn('cpsp_opened_by_seat_id');
            }
            if (Schema::hasColumn('petitions', 'cpsp_opened_by_user_id')) {
                $table->dropForeign(['cpsp_opened_by_user_id']);
                $table->dropColumn('cpsp_opened_by_user_id');
            }
            if (Schema::hasColumn('petitions', 'cpsp_opened_at')) {
                $table->dropColumn('cpsp_opened_at');
            }
        });
    }
};
