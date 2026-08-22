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
            $table->renameColumn('petition_no', 'receipt_no');
            $table->string('file_no')->nullable()->unique()->after('status');
            $table->date('file_created_date')->nullable()->after('file_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn(['file_no', 'file_created_date']);
            $table->renameColumn('receipt_no', 'petition_no');
        });
    }
};
