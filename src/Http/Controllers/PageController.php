<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\FieldExtractor;
use Alumkit\Alumkit\Http\Requests\UpdatePageRequest;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(ContentRegistry $registry): View
    {
        foreach ($registry->getPages() as $slug => $schema) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => ucfirst(str_replace('-', ' ', $slug))],
            );
        }

        $pages = Page::whereIn('slug', array_keys($registry->getPages()))
            ->latest()
            ->get();

        /** @var View $view */
        $view = view('alumkit::pages.index', compact('pages'));

        return $view;
    }

    public function edit(Page $page, ContentRegistry $registry): View
    {
        $schema = $registry->getPage($page->slug);

        $contents = Content::forPage($page->slug)->get()->keyBy('type');

        /** @var View $view */
        $view = view('alumkit::pages.edit', [
            'page' => $page,
            'schema' => $schema,
            'contents' => $contents,
        ]);

        return $view;
    }

    public function update(UpdatePageRequest $request, Page $page, ContentRegistry $registry): RedirectResponse
    {
        $page->update([
            ...$request->validated(),
            'is_published' => $request->boolean('is_published'),
        ]);

        $schema = $registry->getPage($page->slug);

        if ($schema !== null) {
            $owner = "page:{$page->slug}";

            $extractor = new FieldExtractor($request);

            DB::transaction(function () use ($schema, $owner, $extractor): void {
                foreach ($schema->sections() as $type => $section) {
                    $fields = $extractor->extract($section->fields(), $owner, $type);

                    Content::updateOrCreate(
                        ['owner' => $owner, 'type' => $type],
                        ['fields' => $fields],
                    );
                }
            });
        }

        return redirect()->route('alumkit.pages.edit', $page)
            ->with('status', 'Page updated successfully.');
    }
}
