<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
        'manage globals',
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
}
