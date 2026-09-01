<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\SectionSchema;
use Alumkit\Alumkit\Http\Requests\StorePageRequest;
use Alumkit\Alumkit\Http\Requests\UpdatePageRequest;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

            DB::transaction(function () use ($schema, $owner, $request): void {
                foreach ($schema->sections() as $type => $section) {
                    $fields = $this->extractFields($section, $request);
                    $imageFields = $this->extractImages($section, $request);
                    $fields = array_merge($fields, $imageFields);

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

    public function destroy(Page $page): RedirectResponse
    {
        DB::transaction(function () use ($page): void {
            Content::where('owner', "page:{$page->slug}")->delete();
            $page->delete();
        });

        return redirect()->route('alumkit.pages.index')
            ->with('status', 'Page deleted successfully.');
    }

    /** @return array<string, mixed> */
    protected function extractFields(SectionSchema $section, Request $request): array
    {
        $fields = [];

        foreach ($section->fields() as $field) {
            if ($field->type === 'image') {
                continue;
            }

            if ($field->type === 'checkbox') {
                $fields[$field->name] = $request->boolean("fields.{$field->name}");

                continue;
            }

            if ($field->type === 'repeater') {
                $fields[$field->name] = $request->input("fields.{$field->name}", []);

                continue;
            }

            $fields[$field->name] = $request->input("fields.{$field->name}", '');
        }

        return $fields;
    }

    /** @return array<string, string|null> */
    protected function extractImages(SectionSchema $section, Request $request): array
    {
        $images = [];

        foreach ($section->fields() as $field) {
            if ($field->type !== 'image') {
                continue;
            }

            if ($request->hasFile("fields.{$field->name}")) {
                $path = $request->file("fields.{$field->name}")->store('content-images', 'public');
                $images[$field->name] = is_string($path) ? $path : null;
            }
        }

        return $images;
    }
}
