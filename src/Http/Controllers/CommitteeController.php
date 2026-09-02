<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreCommitteeMemberRequest;
use Alumkit\Alumkit\Http\Requests\UpdateCommitteeMemberRequest;
use Alumkit\Alumkit\Models\CommitteeMember;
use Alumkit\Alumkit\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function index(): View
    {
        $members = CommitteeMember::with(['position', 'user'])->orderBy('sort_order')->get();

        /** @var View $view */
        $view = view('alumkit::committee.index', compact('members'));

        return $view;
    }

    public function create(): View
    {
        $positions = Position::all();

        /** @var View $view */
        $view = view('alumkit::committee.create', compact('positions'));

        return $view;
    }

    public function store(StoreCommitteeMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('committee-photos', 'public');
            abort_unless(is_string($path), 500);
            $data['photo_path'] = $path;
        }

        unset($data['photo']);

        $maxOrder = CommitteeMember::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        CommitteeMember::create($data);

        return redirect()->route('alumkit.committee.index')
            ->with('status', __('alumkit::committee.member_created'));
    }

    public function edit(CommitteeMember $committee): View
    {
        $positions = Position::all();

        /** @var View $view */
        $view = view('alumkit::committee.edit', ['member' => $committee, 'positions' => $positions]);

        return $view;
    }

    public function update(UpdateCommitteeMemberRequest $request, CommitteeMember $committee): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($committee->photo_path) {
                Storage::disk('public')->delete($committee->photo_path);
            }

            $path = $request->file('photo')->store('committee-photos', 'public');
            abort_unless(is_string($path), 500);
            $data['photo_path'] = $path;
        }

        unset($data['photo']);

        $committee->update($data);

        return redirect()->route('alumkit.committee.index')
            ->with('status', __('alumkit::committee.member_updated'));
    }

    public function destroy(CommitteeMember $committee): RedirectResponse
    {
        if ($committee->photo_path) {
            Storage::disk('public')->delete($committee->photo_path);
        }

        $committee->delete();

        return redirect()->route('alumkit.committee.index')
            ->with('status', __('alumkit::committee.member_deleted'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array']);

        foreach ($ids['ids'] as $position => $id) {
            CommitteeMember::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
