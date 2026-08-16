<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreProfileEducationRequest;
use Alumkit\Alumkit\Http\Requests\UpdateProfileEducationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ProfileEducationController extends Controller
{
    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::profile.educations.create');

        return $view;
    }

    public function store(StoreProfileEducationRequest $request): RedirectResponse
    {
        $request->user()->educations()->create($request->validated());

        return redirect(route('alumkit.profile').'#education')
            ->with('status', __('alumkit::education.education_created'));
    }

    public function edit(Request $request, int $education): View
    {
        $education = $request->user()->educations()->findOrFail($education);

        /** @var View $view */
        $view = view('alumkit::profile.educations.edit', compact('education'));

        return $view;
    }

    public function update(UpdateProfileEducationRequest $request, int $education): RedirectResponse
    {
        $education = $request->user()->educations()->findOrFail($education);
        $education->update($request->validated());

        return redirect(route('alumkit.profile').'#education')
            ->with('status', __('alumkit::education.education_updated'));
    }

    public function destroy(Request $request, int $education): RedirectResponse
    {
        $request->user()->educations()->findOrFail($education)->delete();

        return redirect(route('alumkit.profile').'#education')
            ->with('status', __('alumkit::education.education_deleted'));
    }
}
