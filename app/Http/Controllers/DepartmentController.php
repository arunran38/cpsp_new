<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::paginate(10);
        return view('admin.department_add', compact('departments'));
    }

    public function create(): View
    {
        $departments = Department::paginate(10);
        return view('admin.department_add', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['department_name' => 'required|string|max:255']);
        try {
            Department::create($request->all());
            Alert::success('Success', 'Department created successfully.');
            return redirect()->route('admin.departments.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Failed to create department.');
            return back()->with('error', 'Failed to create department: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(int $id): View
    {
        $department = Department::findOrFail($id);
        return view('admin.department_edit', compact('department'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate(['department_name' => 'required|string|max:255']);
        try {
            $department = Department::findOrFail($id);
            $department->update($request->all());
            return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update department: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete department: ' . $e->getMessage());
        }
    }
}
