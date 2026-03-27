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
        $assignments = SeatUser::with(['user', 'seat'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
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

        try {
            $userId = $request->user_id;
            $seatId = $request->seat_id;
            $isAdditional = $request->boolean('is_additional');

            // Check if THIS user already has THIS seat active
            $existingUserSeat = SeatUser::where('user_id', $userId)
                ->where('seat_id', $seatId)
                ->where('is_active', true)
                ->exists();

            if ($existingUserSeat) {
                return back()->withErrors(['seat_id' => 'This user is already assigned to this seat.'])->withInput();
            }

            // A seat can only have ONE active user. Revoke current occupant regardless of additional/primary.
            SeatUser::where('seat_id', $seatId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);

            // If NOT an additional charge, revoke existing active primary assignments for this user
            if (!$isAdditional) {
                SeatUser::where('user_id', $userId)
                    ->where('is_active', true)
                    ->where('is_additional', false)
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
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign seat: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $assignment = SeatUser::findOrFail($id);
            
            $wasAdditional = $assignment->is_additional;
            $seatId = $assignment->seat_id;

            $assignment->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);

            if ($wasAdditional) {
                // Find the most recent primary assignment for this seat that was revoked
                $previousPrimary = SeatUser::where('seat_id', $seatId)
                    ->where('is_additional', false)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($previousPrimary) {
                    // Reactivate the original user's assignment
                    $previousPrimary->update([
                        'is_active' => true,
                        'revoked_at' => null,
                    ]);
                }
            }

            return redirect()->route('admin.seatuser.index')->with('success', 'Seat assignment revoked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to revoke assignment: ' . $e->getMessage());
        }
    }
}
