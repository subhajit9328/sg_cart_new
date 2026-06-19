<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Resolve a Role by its ULID from the route segment.
     * Spatie's Role model does not support HasUlids, so we resolve manually.
     */
    private function resolveRole(string $ulid): Role
    {
        return Role::where('ulid', $ulid)->firstOrFail();
    }

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->latest()->paginate(10)->withQueryString();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions'    => ['nullable', 'array'],
            'permissions.*'  => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'web',
            'ulid'       => (string) \Illuminate\Support\Str::ulid(),
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     * Route receives the ULID string — resolved manually since Spatie Role is a 3rd-party model.
     */
    public function edit(string $ulid)
    {
        $role              = $this->resolveRole($ulid);
        $permissions       = Permission::all();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, string $ulid)
    {
        $role = $this->resolveRole($ulid);

        $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Prevent renaming Super Admin for safety
        if ($role->name === 'Super Admin' && $request->name !== 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role name cannot be changed.');
        }

        $role->name = $request->name;
        $role->save();

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(string $ulid)
    {
        $role = $this->resolveRole($ulid);

        // Prevent deleting Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role cannot be deleted.');
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
