<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StorePostRequest;
use Alumkit\Alumkit\Http\Requests\UpdatePostRequest;
use Alumkit\Alumkit\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::where('user_id', $request->user()->id)->latest()->get();

        /** @var View $view */
        $view = view('alumkit::posts.index', compact('posts'));

        return $view;
    }

    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::posts.create');

        return $view;
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        Post::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'published_at' => $request->boolean('published') ? now() : null,
        ]);

        return redirect()->route('alumkit.posts.index')
            ->with('status', __('alumkit::post.post_created'));
    }

    public function edit(Post $post, Request $request): View
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        /** @var View $view */
        $view = view('alumkit::posts.edit', compact('post'));

        return $view;
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->update([
            ...$request->validated(),
            'published_at' => $request->boolean('published') ? now() : null,
        ]);

        return redirect()->route('alumkit.posts.index')
            ->with('status', __('alumkit::post.post_updated'));
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->delete();

        return redirect()->route('alumkit.posts.index')
            ->with('status', __('alumkit::post.post_deleted'));
    }
}
