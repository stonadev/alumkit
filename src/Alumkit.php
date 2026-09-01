<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Alumkit\Alumkit\Models\Post;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Alumkit
{
    /**
     * Package-defined permissions. Always seeded; cannot be removed by the consumer app.
     */
    public const array PERMISSIONS = [
        'manage roles',
        'manage permissions',
        'manage members',
        'manage educations',
        'manage pages',
        'view dashboard',
    ];

    /**
     * Published posts, newest first, author eager-loaded. Compose further (paginate, filter) on the builder.
     *
     * @return Builder<Post>
     */
    public function publishedPosts(): Builder
    {
        return Post::published()->with('user')->latest();
    }

    /**
     * The N most recent published posts (author eager-loaded). Limit is clamped to >= 0.
     *
     * @return Collection<int, Post>
     */
    public function recentPosts(int $limit = 5): Collection
    {
        return $this->publishedPosts()->limit(max(0, $limit))->get();
    }

    /**
     * Register a page schema.
     */
    public function page(string $slug, callable $callback): self
    {
        app(ContentRegistry::class)->registerPage($slug, $callback);

        return $this;
    }

    /**
     * Register a global schema.
     */
    public function global(string $key, callable $callback): self
    {
        app(ContentRegistry::class)->registerGlobal($key, $callback);

        return $this;
    }

    /**
     * Get all registered page schemas.
     *
     * @return array<string, PageSchema>
     */
    public function pages(): array
    {
        return app(ContentRegistry::class)->getPages();
    }

    /**
     * Get all registered global schemas.
     *
     * @return array<string, GlobalSchema>
     */
    public function globals(): array
    {
        return app(ContentRegistry::class)->getGlobals();
    }

    /**
     * Get content for a page.
     *
     * @return Collection<int, Content>
     */
    public function getPageContent(string $slug): Collection
    {
        return Content::forPage($slug)->get();
    }

    /**
     * Get content for a global.
     *
     * @return Collection<int, Content>
     */
    public function getGlobalContent(string $key): Collection
    {
        return Content::forGlobal($key)->get();
    }

    /**
     * Closure for a public page route. Renders the page through its registered
     * schema view; unpublished pages 404 for everyone except users holding the
     * "manage pages" permission (preview).
     *
     * @return Closure(Request): View
     */
    public function pageRoute(string $slug): Closure
    {
        return function (Request $request) use ($slug) {
            $page = Page::where('slug', $slug)->first();
            $viewName = app(ContentRegistry::class)->getPage($slug)?->viewName();

            if (
                $page === null
                || $viewName === null
                || (! $page->is_published && ! $request->user()?->can('manage pages'))
            ) {
                abort(404);
            }

            $contents = Content::forPage($slug)->get()->keyBy('type');

            /** @phpstan-ignore argument.type */
            return view($viewName, compact('page', 'contents'));
        };
    }
}
