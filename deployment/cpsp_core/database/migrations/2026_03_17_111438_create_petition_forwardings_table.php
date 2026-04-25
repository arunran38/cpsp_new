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
        Schema::create('petition_forwardings', function (Blueprint $table) {
            // Primary Key
            $table->id('petition_forwarding_id');

            // Foreign Keys (explicit definition for custom PKs)

            $table->unsignedBigInteger('petition_id');
            $table->unsignedBigInteger('from_seat_id');
            $table->unsignedBigInteger('to_unit_id');

            // Foreign Key Constraints
            $table->foreign('petition_id')
                ->references('petition_id')
                ->on('petitions')
                ->cascadeOnDelete();

            $table->foreign('from_seat_id')
                ->references('seat_id')
                ->on('seats')
                ->cascadeOnDelete();

            $table->foreign('to_unit_id')
                ->references('unit_id')
                ->on('units')
                ->cascadeOnDelete();

            // Other Fields
            $table->text('director_remarks')->nullable();
            $table->date('forwarded_date');
            $table->string('vr_ref_no')->nullable();
            $table->date('vr_date')->nullable();
            $table->date('vr_received_at_cpsp_date')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petition_forwardings');
    }
};
