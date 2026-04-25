<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(): View
    {
        $units = Unit::paginate(10);
        return view('admin.unit_add', compact('units'));
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create(): View
    {
        $units = Unit::paginate(10);
        return view('admin.unit_add', compact('units'));
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        try {
            Unit::create($request->validated());
            Alert::success('Success', 'Unit created successfully.');
            return redirect()->route('admin.units.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Failed to create unit.');
            return back()->with('error', 'Failed to create unit: ' . $e->getMessage())->withInput();
        }
    }   

    /**
     * Show the form for editing the specified unit.
     */
    public function edit(int $id): View
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit_edit', compact('unit'));
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(UpdateUnitRequest $request, int $id): RedirectResponse
    {
        try {
            $unit = Unit::findOrFail($id);
            $unit->update($request->validated());
            return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update unit: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $unit = Unit::findOrFail($id);
            $unit->delete();
            return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete unit: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to check unit code existence.
     */
    public function checkUnitCode(Request $request): JsonResponse
    {
        $request->validate(['unit_code' => 'required|string|max:255']);
        return response()->json(['exists' => Unit::where('unit_code', $request->unit_code)->exists()]);
    }
}
