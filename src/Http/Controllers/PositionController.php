<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StorePositionRequest;
use Alumkit\Alumkit\Http\Requests\UpdatePositionRequest;
use Alumkit\Alumkit\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::withCount('committeeMembers')->get();

        /** @var View $view */
        $view = view('alumkit::positions.index', compact('positions'));

        return $view;
    }

    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::positions.create');

        return $view;
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return redirect()->route('alumkit.positions.index')
            ->with('status', __('alumkit::committee.position_created'));
    }

    public function edit(Position $position): View
    {
        /** @var View $view */
        $view = view('alumkit::positions.edit', compact('position'));

        return $view;
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()->route('alumkit.positions.index')
            ->with('status', __('alumkit::committee.position_updated'));
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('alumkit.positions.index')
            ->with('status', __('alumkit::committee.position_deleted'));
    }
}
