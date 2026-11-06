<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->foreignId('created_by_seat_id')
                ->nullable()
                ->after('seat_id')
                ->constrained('seats', 'seat_id')
                ->nullOnDelete();
        });

        // Backfill existing petitions with their inward creation seat:
        // 1. Identify all Inward seats (by name or role)
        $inwardSeats = DB::table('seats')
            ->where(function ($q) {
                $q->where('seat_name', 'like', '%inward%')
                  ->orWhere('seat_name', 'like', '%Inward%');
            })
            ->pluck('seat_id')
            ->toArray();

        $inwardRoleSeatIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\Seat')
            ->whereIn('roles.name', ['inward', 'Inward', 'INWARD'])
            ->pluck('model_has_roles.model_id')
            ->toArray();

        $allInwardSeatIds = array_values(array_unique(array_merge($inwardSeats, $inwardRoleSeatIds)));

        if (!empty($allInwardSeatIds)) {
            $firstInwardSeatId = $allInwardSeatIds[0];

            // A) Petitions where cpsp_opened_by_seat_id is an inward seat
            DB::table('petitions')
                ->whereIn('cpsp_opened_by_seat_id', $allInwardSeatIds)
                ->update(['created_by_seat_id' => DB::raw('cpsp_opened_by_seat_id')]);

            // B) Petitions created by users who have an Inward seat assignment
            $userInwardSeats = DB::table('seat_users')
                ->whereIn('seat_id', $allInwardSeatIds)
                ->pluck('seat_id', 'user_id')
                ->toArray();

            foreach ($userInwardSeats as $userId => $inwardSeatId) {
                DB::table('petitions')
                    ->whereNull('created_by_seat_id')
                    ->where('user_id', $userId)
                    ->update(['created_by_seat_id' => $inwardSeatId]);
            }

            // C) Petitions marked with General Inward nature or not cpsp processed
            DB::table('petitions')
                ->whereNull('created_by_seat_id')
                ->where(function ($q) {
                    $q->where('nature_of_petition', 'General Inward')
                      ->orWhere('is_cpsp_processed', false);
                })
                ->update(['created_by_seat_id' => $firstInwardSeatId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropForeign(['created_by_seat_id']);
            $table->dropColumn('created_by_seat_id');
        });
    }
};

