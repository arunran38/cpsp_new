<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class DesignationController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canAccess('view designations'), 403, 'Unauthorized. Permission view designations required.');
        $designations = Designation::paginate(10);
        return view('admin.designation_add', compact('designations'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->canAccess('create designations'), 403, 'Unauthorized. Permission create designations required.');
        $designations = Designation::paginate(10);
        return view('admin.designation_add', compact('designations'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('create designations'), 403, 'Unauthorized. Permission create designations required.');
        $request->validate(['designation_name' => 'required|string|max:255']);
        try {
            Designation::create($request->all());
            Alert::success('Success', 'Designation created successfully.');
            return redirect()->route('admin.designations.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Failed to create designation.');
            return back()->with('error', 'Failed to create designation: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(int $id): View
    {
        abort_unless(auth()->user()->canAccess('update designations'), 403, 'Unauthorized. Permission update designations required.');
        $designation = Designation::findOrFail($id);
        return view('admin.designation_edit', compact('designation'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('update designations'), 403, 'Unauthorized. Permission update designations required.');
        $request->validate(['designation_name' => 'required|string|max:255']);
        try {
            $designation = Designation::findOrFail($id);
            $designation->update($request->all());
            return redirect()->route('admin.designations.index')->with('success', 'Designation updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update designation: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('delete designations'), 403, 'Unauthorized. Permission delete designations required.');
        try {
            $designation = Designation::findOrFail($id);
            $designation->delete();
            return redirect()->route('admin.designations.index')->with('success', 'Designation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete designation: ' . $e->getMessage());
        }
    }
}
