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
            if (!Schema::hasColumn('petitions', 'is_returned_to_inward')) {
                $table->boolean('is_returned_to_inward')->default(false)->after('is_cpsp_processed');
            }
            if (!Schema::hasColumn('petitions', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('is_returned_to_inward');
            }
            if (!Schema::hasColumn('petitions', 'returned_by')) {
                $table->unsignedBigInteger('returned_by')->nullable()->after('return_reason');
                $table->foreign('returned_by')->references('user_id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('petitions', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('returned_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'returned_by')) {
                $table->dropForeign(['returned_by']);
                $table->dropColumn('returned_by');
            }
            if (Schema::hasColumn('petitions', 'returned_at')) {
                $table->dropColumn('returned_at');
            }
            if (Schema::hasColumn('petitions', 'return_reason')) {
                $table->dropColumn('return_reason');
            }
            if (Schema::hasColumn('petitions', 'is_returned_to_inward')) {
                $table->dropColumn('is_returned_to_inward');
            }
        });
    }
};
