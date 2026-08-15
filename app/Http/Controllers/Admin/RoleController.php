<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        app(AuditLogService::class)->logWithSubject('roles.created', $role, $role->toArray(), $request);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = \App\Models\Permission::orderBy('group')->orderBy('name')->get();
        $grouped = $permissions->groupBy('group');

        return view('admin.roles.edit', compact('role', 'permissions', 'grouped'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        if ($role->is_system_role && $role->slug === 'super-administrator') {
            $currentUser = auth()->user();
            if (! $currentUser->isSuperAdministrator()) {
                abort(403, 'Only Super Administrators can modify the Super Admin role.');
            }

            $newPermissions = $request->input('permissions', []);
            $currentPermissions = $role->permissions->pluck('id')->toArray();
            if (count(array_diff($currentPermissions, $newPermissions)) > 0) {
                abort(403, 'Super Admin role permissions cannot be reduced.');
            }
        }

        $role->update($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        app(AuditLogService::class)->logWithSubject('roles.updated', $role, $role->getChanges(), $request);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->is_system_role) {
            abort(403, 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            abort(403, 'Cannot delete a role that has assigned users.');
        }

        $changes = ['name' => $role->name, 'slug' => $role->slug];
        $role->delete();

        app(AuditLogService::class)->logWithSubject('roles.deleted', $role, $changes, $request);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
