<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Models\CommitteeMember;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Alumkit\Alumkit\Models\Page;
use Alumkit\Alumkit\Models\Post;
use Alumkit\Alumkit\Models\User;
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
        'manage committee',
        'manage pages',
        'manage membership plans',
        'manage memberships',
        'view dashboard',
        'view activity log',
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
     * All committee members sorted by dashboard order, with position and user eager-loaded.
     *
     * @return Builder<CommitteeMember>
     */
    public function committeeMembers(): Builder
    {
        return CommitteeMember::with(['position', 'user'])->orderBy('sort_order');
    }

    /**
     * The N most recent committee members (sorted by dashboard order). Limit 0 returns all.
     *
     * @return Collection<int, CommitteeMember>
     */
    public function recentCommitteeMembers(int $limit = 0): Collection
    {
        $query = $this->committeeMembers();

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
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
     * Active membership plans, ordered for display. Pricing/gating reads only —
     * plans are authored in the dashboard.
     *
     * @return Collection<int, MembershipPlan>
     */
    public function activePlans(): Collection
    {
        return MembershipPlan::active()->get();
    }

    /**
     * The user's current membership, or null when they have none.
     */
    public function membershipFor(User $user): ?Membership
    {
        /** @var Membership|null $membership */
        $membership = $user->activeMembership()->with('plan')->first();

        return $membership;
    }

    /**
     * Whether the user currently holds an active membership.
     */
    public function hasActiveMembership(User $user): bool
    {
        return $user->hasActiveMembership();
    }

    /**
     * Format an amount in the app-wide membership currency, e.g. "BDT 1,500.00".
     */
    public function formatMoney(float|string $amount): string
    {
        $currency = (string) config('alumkit.membership.currency', 'BDT');

        return $currency.' '.number_format((float) $amount, 2);
    }

    /**
     * The dashboard-managed payment methods (type => label). Only methods
     * staff created in the dashboard are listed.
     *
     * @return array<string, string>
     */
    public function paymentMethods(): array
    {
        /** @var array<string, string> $methods */
        $methods = MembershipPaymentMethod::active()
            ->get()
            ->mapWithKeys(fn (MembershipPaymentMethod $method): array => [$method->type => $method->label()])
            ->all();

        return $methods;
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
