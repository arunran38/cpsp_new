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
            $table->index('status');
            $table->index('date_of_petition_received');
            $table->index('receipt_no');
            $table->index('file_no');
            $table->index('mode_of_petition_received');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->index('person_name');
            $table->index('phone');
            $table->index('person_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['person_name']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['person_type']);
        });

        Schema::table('petitions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['date_of_petition_received']);
            $table->dropIndex(['receipt_no']);
            $table->dropIndex(['file_no']);
            $table->dropIndex(['mode_of_petition_received']);
        });
    }
};
