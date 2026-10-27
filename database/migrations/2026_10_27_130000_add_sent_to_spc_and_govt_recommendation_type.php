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
        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Sent_to_SPC', 'Internal_Vigilance', 'Closed', 'Closed_by_Govt', 'Duplicate') DEFAULT 'Received'");

        Schema::table('decisions', function (Blueprint $table) {
            $table->string('govt_recommendation_type', 100)->nullable()->after('final_decision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn('govt_recommendation_type');
        });

        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Internal_Vigilance', 'Closed', 'Closed_by_Govt', 'Duplicate') DEFAULT 'Received'");
    }
};
