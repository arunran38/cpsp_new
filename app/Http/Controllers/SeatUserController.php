<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\User;
use App\Models\SeatUser;
use Illuminate\Http\Request;

class SeatUserController extends Controller
{
    public function index()
    {
        $assignments = SeatUser::with(['user', 'seat'])->orderBy('created_at', 'desc')->get();
        return view('admin.seatuser_view', compact('assignments'));
    }

    public function create()
    {
        $users = User::all();
        $seats = Seat::where('is_active', true)->get();
        return view('admin.seatuser_add', compact('users', 'seats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'seat_id' => 'required|exists:seats,seat_id',
            'is_additional' => 'nullable|boolean',
        ]);

        $userId = $request->user_id;
        $seatId = $request->seat_id;
        $isAdditional = $request->boolean('is_additional');

        // If NOT an additional charge, revoke existing active assignments for this user AND the current occupant of this seat
        if (!$isAdditional) {
            // Revoke current user's other active seats
            SeatUser::where('user_id', $userId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);

            // Revoke previous occupant of this seat
            SeatUser::where('seat_id', $seatId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);
        }

        SeatUser::create([
            'user_id' => $userId,
            'seat_id' => $seatId,
            'is_additional' => $isAdditional,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        $message = 'Seat assigned successfully' . ($isAdditional ? ' as additional charge.' : '.');
        return redirect()->route('admin.seatuser.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $assignment = SeatUser::findOrFail($id);
        $assignment->update([
            'is_active' => false,
            'revoked_at' => now(),
        ]);

        return redirect()->route('admin.seatuser.index')->with('success', 'Seat assignment revoked successfully.');
    }
}
