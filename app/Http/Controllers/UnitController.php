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
use App\Support\SafeExceptionHandler;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(): View
    {
        abort_unless(auth()->user()->canAccess('view units'), 403, 'Unauthorized. Permission view units required.');
        $units = Unit::paginate(10);
        return view('admin.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->canAccess('create units'), 403, 'Unauthorized. Permission create units required.');
        $units = Unit::paginate(10);
        return view('admin.units.index', compact('units'));
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('create units'), 403, 'Unauthorized. Permission create units required.');
        try {
            Unit::create($request->validated());
            Alert::success('Success', 'Unit created successfully.');
            return redirect()->route('admin.units.index');
        } catch (\Exception $e) {
            $msg = SafeExceptionHandler::handle($e, 'Unit creation failed due to a database error.', 'UnitController@store');
            Alert::error('Error', $msg);
            return back()->with('error', $msg)->withInput();
        }
    }   

    /**
     * Show the form for editing the specified unit.
     */
    public function edit(int $id): View
    {
        abort_unless(auth()->user()->canAccess('update units'), 403, 'Unauthorized. Permission update units required.');
        $unit = Unit::findOrFail($id);
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(UpdateUnitRequest $request, int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('update units'), 403, 'Unauthorized. Permission update units required.');
        try {
            $unit = Unit::findOrFail($id);
            $unit->update($request->validated());
            return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            $msg = SafeExceptionHandler::handle($e, 'Failed to update unit due to a database error.', 'UnitController@update');
            return back()->with('error', $msg)->withInput();
        }
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('delete units'), 403, 'Unauthorized. Permission delete units required.');
        try {
            $unit = Unit::findOrFail($id);
            $unit->delete();
            return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            $msg = SafeExceptionHandler::handle($e, 'Failed to delete unit due to a database error.', 'UnitController@destroy');
            return back()->with('error', $msg);
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
