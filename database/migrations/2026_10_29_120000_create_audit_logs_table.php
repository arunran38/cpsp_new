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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('seat_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('user_pen')->nullable();
            $table->string('action', 50)->index(); // SEARCH, CREATE, READ, UPDATE, DELETE, EXPORT, LOGIN, LOGOUT
            $table->string('module', 50)->index(); // Petitions, Users, Units, Forwardings, Decisions, Compliances, Reports, etc.
            $table->text('description');
            $table->string('method', 10)->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

