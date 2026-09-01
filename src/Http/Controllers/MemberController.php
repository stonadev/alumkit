<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');

        $users = $userModel::query()
            ->where('state', UserState::Active->value)
            ->with(['profile.educations', 'profile.careers'])
            ->orderBy('name')
            ->get();

        /** @var View $view */
        $view = view('alumkit::members.index', [
            'users' => $users,
        ]);

        return $view;
    }

    public function show(string $user): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');

        $user = $userModel::query()
            ->with(['profile.educations', 'profile.careers'])
            ->where('state', UserState::Active->value)
            ->findOrFail($user);

        /** @var View $view */
        $view = view('alumkit::members.show', compact('user'));

        return $view;
    }
}
