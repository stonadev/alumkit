<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\EducationLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompleteProfileController extends Controller
{
    public function create(): View
    {
        $levels = config('alumkit.education.levels', []);

        /** @var View $view */
        $view = view('alumkit::auth.complete-profile', compact('levels'));

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
        ]);

        $user = $request->user();

        foreach ($validated['educations'] as $education) {
            /** @phpstan-ignore method.notFound */
            $user->educations()->create($education);
        }

        return redirect()->route('alumkit.dashboard')
            ->with('status', __('alumkit::auth.profile_completed'));
    }
}
