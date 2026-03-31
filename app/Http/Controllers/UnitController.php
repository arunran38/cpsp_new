<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::paginate(10);
        return view('admin.unit_add', compact('units'));
    }

    public function create()
    {
        $units = Unit::paginate(10);
        return view('admin.unit_add', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:255|unique:units,unit_code',
        ]);

        try {
            Unit::create($request->only('unit_name', 'unit_code'));
            return redirect()->route('admin.units.index')->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create unit: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit_edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:255|unique:units,unit_code,' . $unit->unit_id . ',unit_id',
        ]);

        try {
            $unit->update($request->only('unit_name', 'unit_code'));
            return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update unit: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $unit = Unit::findOrFail($id);
            $unit->delete();
            return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete unit: ' . $e->getMessage());
        }
    }
}
