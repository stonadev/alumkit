<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\SectionSchema;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageContentController extends Controller
{
    public function edit(Page $page, ContentRegistry $registry): View
    {
        $schema = $registry->getPage($page->slug);

        abort_unless($schema !== null, 404, "No schema registered for page '{$page->slug}'.");

        $contents = Content::forPage($page->slug)->get()->keyBy('type');

        /** @var View $view */
        $view = view('alumkit::pages.content', [
            'page' => $page,
            'schema' => $schema,
            'contents' => $contents,
        ]);

        return $view;
    }

    public function update(Page $page, Request $request, ContentRegistry $registry): RedirectResponse
    {
        $schema = $registry->getPage($page->slug);

        abort_unless($schema !== null, 404, "No schema registered for page '{$page->slug}'.");

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

        return redirect()->route('alumkit.pages.content.edit', $page)
            ->with('status', 'Page content updated successfully.');
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
