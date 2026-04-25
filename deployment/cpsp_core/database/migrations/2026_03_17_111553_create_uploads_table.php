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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id('upload_id');
            // Foreign key for petition
            $table->foreignId('petition_id')
                ->constrained('petitions', 'petition_id')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('uploadable_id');
            $table->string('uploadable_type');
            $table->enum('category', [
                'Profile Photo',
                'Petition Document',
                'Verification Report',
                'Final Order',
                'Others',
            ]);
            $table->string('original_filename');
            $table->string('file_path');
            $table->foreignId('uploaded_by')
                ->constrained('users', 'user_id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
