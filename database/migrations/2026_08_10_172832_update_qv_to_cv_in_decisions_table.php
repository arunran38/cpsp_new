<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('decisions')->where('decision_remarks', 'QV')->update(['decision_remarks' => 'CV']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('decisions')->where('decision_remarks', 'CV')->update(['decision_remarks' => 'QV']);
    }
};
