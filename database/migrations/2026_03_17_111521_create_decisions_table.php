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
        Schema::create('decisions', function (Blueprint $table) {
            $table->id('decision_id');
            $table->foreignId('petition_id')->constrained('petitions', 'petition_id')->cascadeOnDelete();
            $table->foreignId('decided_by_seat_id')->constrained('seats', 'seat_id');
            $table->enum('decision_remarks', ['PE', 'SC', 'QV', 'Closed', 'Sent to Govt', 'ICell']);
            $table->text('final_remarks')->nullable();
            $table->date('decision_date');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
