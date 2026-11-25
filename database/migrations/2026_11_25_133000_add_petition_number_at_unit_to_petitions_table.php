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
        Schema::table('petitions', function (Blueprint $table) {
            if (!Schema::hasColumn('petitions', 'petition_number_at_unit')) {
                $table->string('petition_number_at_unit', 30)->nullable()->after('date_of_petition_received');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'petition_number_at_unit')) {
                $table->dropColumn('petition_number_at_unit');
            }
        });
    }
};
