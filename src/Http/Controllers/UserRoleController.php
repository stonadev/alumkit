<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(Request $request): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');

        $filter = $request->query('filter', 'pending');
        $filter = in_array($filter, ['pending', 'all'], true) ? $filter : 'pending';

        $query = $userModel::query()->with(['roles', 'profile.educations', 'profile.careers']);

        if ($filter === 'pending') {
            $query->where('state', UserState::Pending->value)->orderBy('created_at');
        } else {
            $query->orderBy('name');
        }

        /** @var View $view */
        $view = view('alumkit::users.index', [
            'users' => $query->get(),
            'filter' => $filter,
        ]);

        return $view;
    }

    public function show(string $user): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');

        $user = $userModel::query()
            ->with(['roles', 'profile.educations', 'profile.careers'])
            ->findOrFail($user);

        /** @var View $view */
        $view = view('alumkit::users.show', compact('user'));

        return $view;
    }

    public function edit(string $user): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');
        $user = $userModel::findOrFail($user);

        $roles = Role::all();

        /** @var View $view */
        $view = view('alumkit::users.roles', compact('user', 'roles'));

        return $view;
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');
        $targetUser = $userModel::findOrFail($user);

        $request->validate([
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $requestedRoles = $request->input('roles', []);

        // Prevent self-demotion: don't allow removing own admin role
        if ($request->user()->getKey() === $targetUser->getKey()) {
            $defaultRoles = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member']);
            $adminRole = $defaultRoles[0] ?? 'admin';

            if ($targetUser->hasRole($adminRole) && ! in_array($adminRole, $requestedRoles)) {
                return redirect()->route('alumkit.users.roles.edit', $targetUser)
                    ->with('error', __('alumkit::dashboard.cannot_remove_own_admin'));
            }
        }

        $targetUser->syncRoles($requestedRoles);

        return redirect()->route('alumkit.users.roles.edit', $targetUser)
            ->with('status', __('alumkit::dashboard.user_roles_updated'));
    }
}
