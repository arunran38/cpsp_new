<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE petitions MODIFY COLUMN mode_of_petition_received ENUM('Email', 'Whatsapp', 'Tollfree', 'Direct', 'Unit', 'Tapal', 'iaps', 'Government', 'others') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE petitions MODIFY COLUMN mode_of_petition_received ENUM('Email', 'Whatsapp', 'Tollfree', 'Direct', 'Unit', 'Tapal', 'iaps', 'others') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};
