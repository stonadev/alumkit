<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Actions\UpdateProfileDetails;
use Alumkit\Alumkit\Enums\EmploymentType;
use Alumkit\Alumkit\Http\Requests\ProfileDetailsRequest;
use Alumkit\Alumkit\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompleteProfileController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->profile?->isComplete()) {
            return redirect()->route('alumkit.dashboard');
        }

        $employmentTypes = config('alumkit.career.employment_types', []);
        $adminRole = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member'])[0] ?? 'admin';
        $isAdmin = $request->user()->hasRole($adminRole);

        /** @var View $view */
        $view = view('alumkit::auth.complete-profile', compact('employmentTypes', 'isAdmin'));

        return $view;
    }

    public function store(ProfileDetailsRequest $request): RedirectResponse
    {
        if ($request->user()->profile?->isComplete()) {
            return redirect()->route('alumkit.dashboard');
        }

        $validated = $request->validated(); // detail fields (FormRequest)
        $validator = Validator::make($request->all(), [
            'gender' => ['required'],
            'blood_group' => ['required'],
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

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = array_merge($validated, $validator->validated());

        $user = $request->user();

        /** @var Profile $profile */
        $profile = $user->profile()->firstOrCreate();

        $user->setRelation('profile', $profile);

        (new UpdateProfileDetails)->handle($profile, $validated, $request->file('photo'));

        foreach ($validated['careers'] ?? [] as $career) {
            /** @phpstan-ignore method.notFound */
            $user->careers()->create($career);
        }

        activity('profile')->performedOn($user)->event('submitted')->log('profile submitted');

        $adminRole = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member'])[0] ?? 'admin';

        if (! $user->hasRole($adminRole)) {
            return redirect()->route('alumkit.dashboard')
                ->with('status', __('alumkit::auth.profile_completed'));
        }

        return redirect()->route('alumkit.dashboard');
    }
}
