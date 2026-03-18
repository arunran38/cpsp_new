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
        Schema::create('petitions', function (Blueprint $table) {
            $table->id('petition_id');
            $table->string('petition_no')->unique();
            $table->date('date_of_petition_received');
            $table->text('description');
            $table->text('proposed_action')->nullable();
            $table->enum('status', ['Received', 'Forwarded', 'VR_Received', 'Sent_to_Govt', 'Closed'])->default('Received');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petitions');
    }
};
