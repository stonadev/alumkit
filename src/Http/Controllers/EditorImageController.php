<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EditorImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'image', 'max:5120']]);

        // ponytail: uploaded images are never deleted when a post is removed; add cleanup when storage hygiene matters
        $path = $request->file('file')->store('editor-images', 'public');
        abort_unless(is_string($path), 500);

        return response()->json([
            'success' => 1,
            'file' => ['url' => route('alumkit.editor.image.show', basename($path))],
        ]);
    }
}
