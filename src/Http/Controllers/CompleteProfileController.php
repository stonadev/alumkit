<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\EducationLevel;
use Alumkit\Alumkit\Enums\EmploymentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompleteProfileController extends Controller
{
    public function create(Request $request): View
    {
        $levels = config('alumkit.education.levels', []);
        $employmentTypes = config('alumkit.career.employment_types', []);
        $adminRole = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member'])[0] ?? 'admin';
        $isAdmin = $request->user()->hasRole($adminRole);

        /** @var View $view */
        $view = view('alumkit::auth.complete-profile', compact('levels', 'employmentTypes', 'isAdmin'));

        return $view;
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'educations' => ['required', 'array', 'min:1'],
            'educations.*.level' => ['required', Rule::in(array_column(EducationLevel::cases(), 'value'))],
            'educations.*.institution' => ['required', 'string', 'max:255'],
            'educations.*.subject' => ['nullable', 'string', 'max:255'],
            'educations.*.start_year' => ['nullable', 'integer', 'digits:4'],
            'educations.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'educations.*.end_year' => ['nullable', 'integer', 'digits:4', 'gte:educations.*.start_year'],
            'educations.*.end_month' => ['nullable', 'integer', 'between:1,12'],
            'careers' => ['nullable', 'array'],
            'careers.*.job_title' => ['required', 'string', 'max:255'],
            'careers.*.company' => ['required', 'string', 'max:255'],
            'careers.*.employment_type' => ['required', Rule::in(array_column(EmploymentType::cases(), 'value'))],
            'careers.*.industry' => ['nullable', 'string', 'max:255'],
            'careers.*.location' => ['nullable', 'string', 'max:255'],
            'careers.*.start_year' => ['required', 'integer', 'digits:4'],
            'careers.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'careers.*.is_current' => ['boolean'],
            'careers.*.end_year' => ['nullable', 'integer', 'digits:4'],
            'careers.*.end_month' => ['nullable', 'integer', 'between:1,12'],
            'careers.*.description' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $user->profile()->firstOrCreate();

        foreach ($validated['educations'] as $education) {
            /** @phpstan-ignore method.notFound */
            $user->educations()->create($education);
        }

        foreach ($validated['careers'] ?? [] as $career) {
            /** @phpstan-ignore method.notFound */
            $user->careers()->create($career);
        }

        $adminRole = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member'])[0] ?? 'admin';

        if (! $user->hasRole($adminRole)) {
            return redirect()->route('alumkit.dashboard')
                ->with('status', __('alumkit::auth.profile_completed'));
        }

        return redirect()->route('alumkit.dashboard');
    }
}
