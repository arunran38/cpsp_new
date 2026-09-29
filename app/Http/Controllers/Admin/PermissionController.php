<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(10);
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $request->name]);
        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate(['name' => 'required|unique:permissions,name,'.$permission->id]);
        $permission->update(['name' => $request->name]);
        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();
        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
