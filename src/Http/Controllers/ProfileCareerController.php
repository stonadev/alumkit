<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Actions\ResubmitProfileForReview;
use Alumkit\Alumkit\Http\Requests\StoreProfileCareerRequest;
use Alumkit\Alumkit\Http\Requests\UpdateProfileCareerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ProfileCareerController extends Controller
{
    public function create(): View
    {
        $employmentTypes = config('alumkit.career.employment_types', []);

        /** @var View $view */
        $view = view('alumkit::profile.careers.create', compact('employmentTypes'));

        return $view;
    }

    public function store(StoreProfileCareerRequest $request): RedirectResponse
    {
        $request->user()->careers()->create($request->validated());
        (new ResubmitProfileForReview)->handle($request->user());

        return redirect(route('alumkit.profile').'#career')
            ->with('status', __('alumkit::career.career_created'));
    }

    public function edit(Request $request, int $career): View
    {
        $career = $request->user()->careers()->findOrFail($career);
        $employmentTypes = config('alumkit.career.employment_types', []);

        /** @var View $view */
        $view = view('alumkit::profile.careers.edit', compact('career', 'employmentTypes'));

        return $view;
    }

    public function update(UpdateProfileCareerRequest $request, int $career): RedirectResponse
    {
        $career = $request->user()->careers()->findOrFail($career);
        $career->update($request->validated());
        (new ResubmitProfileForReview)->handle($request->user());

        return redirect(route('alumkit.profile').'#career')
            ->with('status', __('alumkit::career.career_updated'));
    }

    public function destroy(Request $request, int $career): RedirectResponse
    {
        $request->user()->careers()->findOrFail($career)->delete();
        (new ResubmitProfileForReview)->handle($request->user());

        return redirect(route('alumkit.profile').'#career')
            ->with('status', __('alumkit::career.career_deleted'));
    }
}
