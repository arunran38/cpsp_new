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
        Schema::create('petition_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 20)->default('create'); // 'create' or 'update'
            $table->unsignedBigInteger('petition_id')->nullable(); // null for 'create'
            $table->unsignedTinyInteger('step')->default(1);
            $table->json('form_data');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('petition_id')->references('petition_id')->on('petitions')->onDelete('cascade');

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'petition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petition_drafts');
    }
};
