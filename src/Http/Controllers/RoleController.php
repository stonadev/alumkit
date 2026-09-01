<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreRoleRequest;
use Alumkit\Alumkit\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->get();

        /** @var View $view */
        $view = view('alumkit::roles.index', compact('roles'));

        return $view;
    }

    public function create(): View
    {
        $permissions = Permission::all();

        /** @var View $view */
        $view = view('alumkit::roles.create', compact('permissions'));

        return $view;
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name')]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->validated('permissions'));
        }

        activity('role_management')
            ->performedOn($role) /** @phpstan-ignore argument.type */
            ->event('created')
            ->withProperties(['permissions' => $request->validated('permissions', [])])
            ->log('role created');

        return redirect()->route('alumkit.roles.index')
            ->with('status', __('alumkit::dashboard.role_created'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::all();

        /** @var View $view */
        $view = view('alumkit::roles.edit', compact('role', 'permissions'));

        return $view;
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $currentPermissions = $role->permissions->pluck('name')->toArray();

        $role->update(['name' => $request->validated('name')]);

        $role->syncPermissions($request->validated('permissions', []));

        $newPermissions = $request->validated('permissions', []);

        activity('role_management')
            ->performedOn($role)
            ->event('updated')
            ->withProperties([
                'permissions_added' => array_values(array_diff($newPermissions, $currentPermissions)),
                'permissions_removed' => array_values(array_diff($currentPermissions, $newPermissions)),
            ])
            ->log('role updated');

        return redirect()->route('alumkit.roles.index')
            ->with('status', __('alumkit::dashboard.role_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()->route('alumkit.roles.index')
                ->with('error', __('alumkit::dashboard.role_has_users'));
        }

        activity('role_management')
            ->performedOn($role)
            ->event('deleted')
            ->withProperties(['role_name' => $role->name])
            ->log('role deleted');

        $role->delete();

        return redirect()->route('alumkit.roles.index')
            ->with('status', __('alumkit::dashboard.role_deleted'));
    }
}
