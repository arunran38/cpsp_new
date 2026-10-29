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
        if (Schema::hasColumn('decisions', 'decision_remarks')) {
            Schema::table('decisions', function (Blueprint $table) {
                $table->renameColumn('decision_remarks', 'final_decision');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->renameColumn('final_decision', 'decision_remarks');
        });
    }
};
