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
            if (!Schema::hasColumn('petitions', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('seat_id');
                $table->foreign('unit_id')->references('unit_id')->on('units')->nullOnDelete();
            }
            if (!Schema::hasColumn('petitions', 'date_of_petition_received_at_unit')) {
                $table->date('date_of_petition_received_at_unit')->nullable()->after('date_of_petition_received');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
            if (Schema::hasColumn('petitions', 'date_of_petition_received_at_unit')) {
                $table->dropColumn('date_of_petition_received_at_unit');
            }
        });
    }
};
