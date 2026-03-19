<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        return view('admin.unit_view', compact('units'));
    }

    public function create()
    {
        return view('admin.unit_add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:255|unique:units,unit_code',
        ]);

        Unit::create($request->all());

        return redirect()->route('admin.units.index')->with('success', 'Unit created successfully.');
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

        $unit->update($request->all());

        return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
    }
}
