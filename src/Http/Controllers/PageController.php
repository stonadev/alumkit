<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StorePageRequest;
use Alumkit\Alumkit\Http\Requests\UpdatePageRequest;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::latest()->get();

        /** @var View $view */
        $view = view('alumkit::pages.index', compact('pages'));

        return $view;
    }

    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::pages.create');

        return $view;
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        Page::create([
            ...$request->validated(),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('alumkit.pages.index')
            ->with('status', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        /** @var View $view */
        $view = view('alumkit::pages.edit', compact('page'));

        return $view;
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update([
            ...$request->validated(),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('alumkit.pages.index')
            ->with('status', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        DB::transaction(function () use ($page): void {
            Content::where('owner', "page:{$page->slug}")->delete();
            $page->delete();
        });

        return redirect()->route('alumkit.pages.index')
            ->with('status', 'Page deleted successfully.');
    }
}
