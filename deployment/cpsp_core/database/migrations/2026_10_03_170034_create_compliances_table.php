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
        Schema::create('compliances', function (Blueprint $table) {
            $table->id('compliance_id');
            $table->unsignedBigInteger('decision_id');
            $table->unsignedBigInteger('petition_id');
            $table->string('action_number');
            $table->date('action_date');
            $table->unsignedBigInteger('unit_id');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('decision_id')->references('decision_id')->on('decisions')->onDelete('cascade');
            $table->foreign('petition_id')->references('petition_id')->on('petitions')->onDelete('cascade');
            $table->foreign('unit_id')->references('unit_id')->on('units');
            $table->foreign('created_by_user_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliances');
    }
};
