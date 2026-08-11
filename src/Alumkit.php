<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

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
}
