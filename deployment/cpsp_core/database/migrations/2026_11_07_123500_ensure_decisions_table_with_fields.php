<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensures all essential columns exist in decisions and other critical tables across all server environments.
     */
    public function up(): void
    {
        // 1. Ensure decisions table integrity
        if (Schema::hasTable('decisions')) {
            Schema::table('decisions', function (Blueprint $table) {
                if (!Schema::hasColumn('decisions', 'directorate_order_number')) {
                    $table->string('directorate_order_number', 255)->nullable();
                }
                if (!Schema::hasColumn('decisions', 'govt_recommendation_type')) {
                    $table->string('govt_recommendation_type', 100)->nullable();
                }
                if (!Schema::hasColumn('decisions', 'final_decision') && !Schema::hasColumn('decisions', 'decision_remarks')) {
                    $table->string('final_decision', 50)->nullable();
                }
            });
        }

        // 2. Ensure petitions table core workflow columns exist
        if (Schema::hasTable('petitions')) {
            Schema::table('petitions', function (Blueprint $table) {
                if (!Schema::hasColumn('petitions', 'created_by_seat_id')) {
                    $table->foreignId('created_by_seat_id')->nullable()->constrained('seats', 'seat_id')->nullOnDelete();
                }
                if (!Schema::hasColumn('petitions', 'previous_status')) {
                    $table->string('previous_status')->nullable();
                }
                if (!Schema::hasColumn('petitions', 'is_cpsp_processed')) {
                    $table->boolean('is_cpsp_processed')->default(false);
                }
                if (!Schema::hasColumn('petitions', 'is_returned_to_inward')) {
                    $table->boolean('is_returned_to_inward')->default(false);
                }
                if (!Schema::hasColumn('petitions', 'return_reason')) {
                    $table->text('return_reason')->nullable();
                }
                if (!Schema::hasColumn('petitions', 'cpsp_opened_at')) {
                    $table->timestamp('cpsp_opened_at')->nullable();
                }
                if (!Schema::hasColumn('petitions', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op down to preserve production data
    }
};

