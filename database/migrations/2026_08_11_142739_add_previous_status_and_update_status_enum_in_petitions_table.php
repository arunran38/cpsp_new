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
        Schema::table('petitions', function (Blueprint $table) {
            $table->string('previous_status')->nullable()->after('status');
        });

        // Update ENUM using DB statement since ENUM alterations are tricky
        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Closed', 'Duplicate') DEFAULT 'Received'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Closed') DEFAULT 'Received'");
        
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn('previous_status');
        });
    }
};
