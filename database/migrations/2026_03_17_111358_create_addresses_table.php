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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->foreignId('petition_id')
                ->constrained('petitions', 'petition_id')
                ->cascadeOnDelete();
            $table->string('person_name');
            $table->enum('person_type', ['Complainant', 'Accused']);
            $table->enum('address_type', ['Permanent', 'Temporary', 'Office']);
            $table->boolean('is_primary')->default(false);
            $table->string('Aadhar_number')->nullable();
            $table->string('phone')->nullable();
            $table->text('full_address');
            $table->string('district');
            $table->string('pincode', 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
