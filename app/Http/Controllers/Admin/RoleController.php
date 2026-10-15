<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->canAccess('view roles'), 403, 'Unauthorized. You do not have permission to view roles.');

        $roles = Role::with('permissions')
            ->orderByRaw("
                CASE 
                    WHEN LOWER(name) = 'developer' THEN 1
                    WHEN LOWER(name) = 'super admin' THEN 2
                    WHEN LOWER(name) = 'admin' THEN 3
                    ELSE 4
                END ASC, name ASC
            ")
            ->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canAccess('create roles'), 403, 'Unauthorized. You do not have permission to create roles.');

        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccess('create roles'), 403, 'Unauthorized. You do not have permission to create roles.');

        $request->validate(['name' => 'required|unique:roles,name']);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->canAccess('update roles'), 403, 'Unauthorized. You do not have permission to edit roles.');

        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->canAccess('update roles'), 403, 'Unauthorized. You do not have permission to update roles.');

        $role = Role::findOrFail($id);
        $request->validate(['name' => 'required|unique:roles,name,'.$role->id]);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->canAccess('delete roles'), 403, 'Unauthorized. You do not have permission to delete roles.');

        $role = Role::findOrFail($id);
        if (in_array(strtolower($role->name), ['developer', 'super admin', 'admin'])) {
            return redirect()->route('admin.roles.index')->with('error', 'Core system roles cannot be deleted.');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
