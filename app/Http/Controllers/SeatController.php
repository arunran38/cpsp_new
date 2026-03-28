<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Unit;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index()
    {
        $seats = Seat::with(['units', 'activeAssignment.user.profilePhoto'])->paginate(10);
        return view('admin.seat_view', compact('seats'));
    }

    public function create()
    {
        $units = Unit::all();
        return view('admin.seat_add', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,unit_id',
            'seat_name' => 'required|string|max:255',
        ]);

        try {
            $seat = Seat::create($request->only('seat_name', 'is_active'));
            $seat->units()->sync($request->unit_ids);
            return redirect()->route('admin.seats.index')->with('success', 'Seat created and units assigned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create seat: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $seat = Seat::with('units')->findOrFail($id);
        $units = Unit::all();
        return view('admin.seat_edit', compact('seat', 'units'));
    }

    public function update(Request $request, $id)
    {
        $seat = Seat::findOrFail($id);

        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,unit_id',
            'seat_name' => 'required|string|max:255',
        ]);

        try {
            $seat->update($request->only('seat_name', 'is_active'));
            $seat->units()->sync($request->unit_ids);
            return redirect()->route('admin.seats.index')->with('success', 'Seat and unit assignments updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update seat: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $seat = Seat::findOrFail($id);

            // Check if seat has active assignment
            $activeAssignment = SeatUser::where('seat_id', '=', $id)
                                        ->where('is_active', '=', true)
                                        ->first();
            
            if ($activeAssignment) {
                return back()->with('error', 'Cannot delete seat: An officer is currently assigned to this seat.');
            }

            $seat->delete();
            return redirect()->route('admin.seats.index')->with('success', 'Seat deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete seat: ' . $e->getMessage());
        }
    }

    public function history($id)
    {
        $seat = Seat::findOrFail($id);
        $assignments = SeatUser::with(['user' => function($q) {
            $q->withTrashed()->with('profilePhoto');
        }])
        ->where('seat_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin.seat_history', compact('seat', 'assignments'));
    }

    public function revokeAssignment($id)
    {
        try {
            SeatUser::where('seat_id', $id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);
            return redirect()->route('admin.seats.index')->with('success', 'Assignment revoked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to revoke assignment: ' . $e->getMessage());
        }
    }
}
