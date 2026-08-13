<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreEducationRequest;
use Alumkit\Alumkit\Http\Requests\UpdateEducationRequest;
use Alumkit\Alumkit\Models\Education;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(): View
    {
        $educations = Education::with('profile')->latest()->get();

        /** @var View $view */
        $view = view('alumkit::educations.index', compact('educations'));

        return $view;
    }

    public function create(): View
    {
        $levels = config('alumkit.education.levels', []);
        $userModel = config('alumkit.auth.user_model');
        $users = $userModel::all();

        /** @var View $view */
        $view = view('alumkit::educations.create', compact('levels', 'users'));

        return $view;
    }

    public function store(StoreEducationRequest $request): RedirectResponse
    {
        Education::create($request->validated());

        return redirect()->route('alumkit.educations.index')
            ->with('status', __('alumkit::education.education_created'));
    }

    public function edit(Education $education): View
    {
        $levels = config('alumkit.education.levels', []);

        /** @var View $view */
        $view = view('alumkit::educations.edit', compact('education', 'levels'));

        return $view;
    }

    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        return redirect()->route('alumkit.educations.index')
            ->with('status', __('alumkit::education.education_updated'));
    }

    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        return redirect()->route('alumkit.educations.index')
            ->with('status', __('alumkit::education.education_deleted'));
    }
}
