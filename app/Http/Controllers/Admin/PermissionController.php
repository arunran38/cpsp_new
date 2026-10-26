<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canAccess('view permissions'), 403, 'Unauthorized. You do not have permission to view permissions.');

        $search = $request->input('search');

        $query = Permission::with('roles');

        if ($search) {
            $s = trim($search);
            $query->where('name', 'like', "%{$s}%");
        }

        // Paginating 25 items per page by default, or all
        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all') {
            $permissions = $query->orderBy('name')->paginate(500)->withQueryString();
        } else {
            $permissions = $query->orderBy('name')->paginate(is_numeric($perPage) ? (int)$perPage : 25)->withQueryString();
        }

        $totalPermissionsCount = Permission::count();
        $totalRolesCount = Role::count();

        return view('admin.permissions.index', compact('permissions', 'totalPermissionsCount', 'totalRolesCount'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canAccess('create permissions'), 403, 'Unauthorized. You do not have permission to create permissions.');

        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccess('create permissions'), 403, 'Unauthorized. You do not have permission to create permissions.');

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name'
        ]);

        $normalizedName = strtolower(trim($request->name));
        Permission::create([
            'name' => $normalizedName,
            'guard_name' => 'web'
        ]);

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$normalizedName}' created successfully.");
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->canAccess('update permissions'), 403, 'Unauthorized. You do not have permission to edit permissions.');

        $permission = Permission::with('roles')->findOrFail($id);
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->canAccess('update permissions'), 403, 'Unauthorized. You do not have permission to update permissions.');

        $permission = Permission::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$permission->id
        ]);

        $normalizedName = strtolower(trim($request->name));
        $permission->update(['name' => $normalizedName]);

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$normalizedName}' updated successfully.");
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->canAccess('delete permissions'), 403, 'Unauthorized. You do not have permission to delete permissions.');

        $permission = Permission::findOrFail($id);

        // Protect core security and confidential T-Section permissions from accidental deletion
        $protected = array_merge([
            'view users', 'create users', 'update users', 'delete users',
            'view roles', 'create roles', 'update roles', 'delete roles',
            'view permissions', 'create permissions', 'update permissions', 'delete permissions',
            'access admin dashboard'
        ], \App\Services\TSectionPermissionRegistry::all());

        if (in_array(strtolower($permission->name), $protected, true)) {
            return redirect()->route('admin.permissions.index')->with('error', "Cannot delete critical system capability '{$permission->name}'.");
        }

        $permissionName = $permission->name;
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permissionName}' removed successfully.");
    }
}
