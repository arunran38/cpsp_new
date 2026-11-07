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
        DB::statement("ALTER TABLE petitions MODIFY COLUMN nature_of_petition ENUM('Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence', 'Amassment of Wealth', 'others') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE petitions MODIFY COLUMN nature_of_petition ENUM('Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence', 'others') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};
