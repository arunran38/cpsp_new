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
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('contact_person');
            $table->foreignId('designation_id')->nullable()->constrained('designation_lists')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('department_lists')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['designation_id', 'department_id']);
            $table->string('contact_person')->nullable()->after('person_name');
        });
    }
};
