<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Models\Content;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class GlobalContentController extends Controller
{
    public function index(ContentRegistry $registry): View
    {
        $globals = $registry->getGlobals();

        /** @var View $view */
        $view = view('alumkit::globals.index', compact('globals'));

        return $view;
    }

    public function edit(string $key, ContentRegistry $registry): View
    {
        $schema = $registry->getGlobal($key);

        abort_unless($schema !== null, 404, "No schema registered for global '{$key}'.");

        $contents = Content::forGlobal($key)->first();

        /** @var View $view */
        $view = view('alumkit::globals.edit', [
            'key' => $key,
            'schema' => $schema,
            'contents' => $contents,
        ]);

        return $view;
    }

    public function update(string $key, Request $request, ContentRegistry $registry): RedirectResponse
    {
        $schema = $registry->getGlobal($key);

        abort_unless($schema !== null, 404, "No schema registered for global '{$key}'.");

        $owner = "global:{$key}";
        $fields = $this->extractFields($schema, $request);
        $imageFields = $this->extractImages($schema, $request);
        $fields = array_merge($fields, $imageFields);

        Content::updateOrCreate(
            ['owner' => $owner, 'type' => 'global'],
            ['fields' => $fields],
        );

        return redirect()->route('alumkit.globals.edit', $key)
            ->with('status', 'Global content updated successfully.');
    }

    /** @return array<string, mixed> */
    protected function extractFields(GlobalSchema $schema, Request $request): array
    {
        $fields = [];

        foreach ($schema->fields() as $field) {
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
    protected function extractImages(GlobalSchema $schema, Request $request): array
    {
        $images = [];

        foreach ($schema->fields() as $field) {
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
