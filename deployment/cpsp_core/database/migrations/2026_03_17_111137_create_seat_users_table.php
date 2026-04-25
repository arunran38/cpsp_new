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
        Schema::create('seat_users', function (Blueprint $table) {
            $table->id('seat_user_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats', 'seat_id')->onDelete('cascade');
            $table->boolean('is_additional')->default(false);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_user');
    }
};
