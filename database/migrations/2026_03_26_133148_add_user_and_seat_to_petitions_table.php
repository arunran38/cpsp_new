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
            $table->foreignId('user_id')->nullable()->after('petition_id')->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('seat_id')->nullable()->after('user_id')->constrained('seats', 'seat_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['seat_id']);
            $table->dropColumn(['user_id', 'seat_id']);
        });
    }
};
