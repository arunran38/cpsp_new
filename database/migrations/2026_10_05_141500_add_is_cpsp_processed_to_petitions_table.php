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
        if (!Schema::hasColumn('petitions', 'is_cpsp_processed')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->boolean('is_cpsp_processed')->default(true)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('petitions', 'is_cpsp_processed')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropColumn('is_cpsp_processed');
            });
        }
    }
};
