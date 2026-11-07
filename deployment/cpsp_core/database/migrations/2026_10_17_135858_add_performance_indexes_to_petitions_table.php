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
            $table->index(['linked_petition_id', 'is_returned_to_inward', 'is_cpsp_processed'], 'idx_petitions_workflow');
            $table->index(['seat_id', 'is_cpsp_processed', 'is_returned_to_inward'], 'idx_petitions_seat_workflow');
            $table->index(['status', 'is_cpsp_processed'], 'idx_petitions_status_processed');
            $table->index(['date_of_petition_received', 'status'], 'idx_petitions_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropIndex('idx_petitions_workflow');
            $table->dropIndex('idx_petitions_seat_workflow');
            $table->dropIndex('idx_petitions_status_processed');
            $table->dropIndex('idx_petitions_date_status');
        });
    }
};
