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
            $table->renameColumn('decision_remarks', 'final_decision');
            $table->string('directorate_order_number')->nullable()->after('final_decision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn('directorate_order_number');
            $table->renameColumn('final_decision', 'decision_remarks');
        });
    }
};
