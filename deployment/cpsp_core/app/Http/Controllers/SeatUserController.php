<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\User;
use App\Models\SeatUser;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SeatUserController extends Controller
{
    /**
     * Display a listing of seat-user assignments.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.seats.index');
    }

    /**
     * Show the form for creating a new seat-user assignment.
     */
    public function create(Request $request): View
    {
        $users = User::all();
        $seats = Seat::where('is_active', true)->get();
        $selectedSeatId = $request->get('seat_id');
        
        return view('admin.seatuser_add', compact('users', 'seats', 'selectedSeatId'));
    }

    /**
     * Store a newly created seat-user assignment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'seat_id' => 'required|exists:seats,seat_id',
            'is_additional' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $userId = $request->user_id;
                $seatId = $request->seat_id;
                $isAdditional = $request->boolean('is_additional');

                // 1. Check if user already has this specific assignment active
                $exists = SeatUser::where('user_id', $userId)
                    ->where('seat_id', $seatId)
                    ->where('is_active', true)
                    ->exists();

                if ($exists) {
                    return back()->withErrors(['seat_id' => 'This user is already assigned to this seat.'])->withInput();
                }

                // 2. Validate primary seat constraint
                if (!$isAdditional) {
                    $hasPrimary = SeatUser::where('user_id', $userId)
                        ->where('is_active', true)
                        ->where('is_additional', false)
                        ->exists();

                    if ($hasPrimary) {
                        return back()->with('error', "User already has a primary seat assigned. Please select 'Additional Charge'.")->withInput();
                    }
                }

                // 3. Revoke current occupant (Seat can only have one active user)
                SeatUser::where('seat_id', $seatId)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'revoked_at' => now()]);

                // 4. Create new assignment
                SeatUser::create([
                    'user_id' => $userId,
                    'seat_id' => $seatId,
                    'is_additional' => $isAdditional,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);

                $suffix = $isAdditional ? ' as additional charge.' : '.';
                return redirect()->route('admin.seatuser.index')->with('success', "Seat assigned successfully$suffix");
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to assign seat: ' . $e->getMessage())->withInput();
            }
        });
    }

    /**
     * Remove the specified seat-user assignment from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        return DB::transaction(function () use ($id) {
            try {
                $assignment = SeatUser::findOrFail($id);
                $wasAdditional = $assignment->is_additional;
                $seatId = $assignment->seat_id;

                $assignment->update(['is_active' => false, 'revoked_at' => now()]);

                // Fallback logic for additional charges
                if ($wasAdditional) {
                    $previousPrimary = SeatUser::where('seat_id', $seatId)
                        ->where('is_additional', false)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($previousPrimary) {
                        $previousPrimary->update(['is_active' => true, 'revoked_at' => null]);
                    }
                }

                return redirect()->route('admin.seatuser.index')->with('success', 'Seat assignment revoked successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to revoke assignment.');
            }
        });
    }
}
