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
        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Internal_Vigilance', 'Closed', 'Closed_by_Govt', 'Duplicate') DEFAULT 'Received'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE petitions MODIFY COLUMN status ENUM('Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Closed', 'Closed_by_Govt', 'Duplicate') DEFAULT 'Received'");
    }
};
