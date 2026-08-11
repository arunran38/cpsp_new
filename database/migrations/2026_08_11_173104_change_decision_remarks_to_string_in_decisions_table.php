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
        Schema::table('decisions', function (Blueprint $table) {
            $table->string('decision_remarks', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Note: Reverting back to ENUM might cause data loss if values exist outside the original ENUM list.
            DB::statement("ALTER TABLE decisions MODIFY decision_remarks ENUM('PE','SC','QV','Closed','Sent to Govt','ICell') DEFAULT NULL");
        });
    }
};
