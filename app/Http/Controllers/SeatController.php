<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Unit;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index()
    {
        $seats = Seat::with('units')->get();
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

        $seat = Seat::create($request->only('seat_name', 'is_active'));
        $seat->units()->sync($request->unit_ids);

        return redirect()->route('admin.seats.index')->with('success', 'Seat created and units assigned successfully.');
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

        $seat->update($request->only('seat_name', 'is_active'));
        $seat->units()->sync($request->unit_ids);

        return redirect()->route('admin.seats.index')->with('success', 'Seat and unit assignments updated successfully.');
    }

    public function destroy($id)
    {
        $seat = Seat::findOrFail($id);
        $seat->delete();

        return redirect()->route('admin.seats.index')->with('success', 'Seat deleted successfully.');
    }
}
