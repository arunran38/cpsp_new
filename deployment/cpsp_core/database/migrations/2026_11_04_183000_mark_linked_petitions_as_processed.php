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
        // Update all existing linked/duplicate petitions to be treated as processed
        DB::table('petitions')
            ->where(function ($q) {
                $q->whereNotNull('linked_petition_id')
                  ->orWhere('status', 'Duplicate');
            })
            ->update([
                'is_cpsp_processed' => true,
                'status' => 'Duplicate',
                'processed_at' => DB::raw('COALESCE(processed_at, NOW())'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed as this is a data normalization migration
    }
};

