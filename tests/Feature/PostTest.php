<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);
});

it('renders the posts index for approved users', function () {
    Post::create(['user_id' => $this->user->id, 'title' => 'My First Post', 'body' => 'Hello']);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.index'))
        ->assertOk()
        ->assertSee('My First Post');
});

it('renders the create post form for approved users', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.posts.create'))
        ->assertOk();
});

it('creates a post as a draft by default', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.posts.store'), [
            'title' => 'Draft Post',
            'body' => 'Not published yet',
        ])
        ->assertRedirect(route('alumkit.posts.index'));

    $this->assertDatabaseHas('posts', [
        'user_id' => $this->user->id,
        'title' => 'Draft Post',
        'body' => 'Not published yet',
        'published_at' => null,
    ]);
});

it('creates a published post when the publish box is checked', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.posts.store'), [
            'title' => 'Published Post',
            'body' => 'Visible to everyone',
            'published' => 1,
        ])
        ->assertRedirect(route('alumkit.posts.index'));

    $this->assertNotNull(Post::where('title', 'Published Post')->first()->published_at);
});

it('validates post required fields', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.posts.store'), [
            'title' => '',
            'body' => '',
        ])
        ->assertSessionHasErrors(['title', 'body']);
});

it('renders the edit post form for the author', function () {
    $post = Post::create(['user_id' => $this->user->id, 'title' => 'Editable', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.edit', $post))
        ->assertOk();
});

it('updates a post', function () {
    $post = Post::create(['user_id' => $this->user->id, 'title' => 'Old Title', 'body' => 'Old body']);

    $this->actingAs($this->user)
        ->put(route('alumkit.posts.update', $post), [
            'title' => 'New Title',
            'body' => 'New body',
        ])
        ->assertRedirect(route('alumkit.posts.index'));

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'New Title',
        'body' => 'New body',
        'published_at' => null,
    ]);
});

it('deletes a post', function () {
    $post = Post::create(['user_id' => $this->user->id, 'title' => 'Doomed', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->delete(route('alumkit.posts.destroy', $post))
        ->assertRedirect(route('alumkit.posts.index'));

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('forbids editing another users post', function () {
    $other = User::factory()->approved()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford']);
    $post = Post::create(['user_id' => $other->id, 'title' => 'Not Mine', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.edit', $post))
        ->assertForbidden();
});

it('forbids updating another users post', function () {
    $other = User::factory()->approved()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford']);
    $post = Post::create(['user_id' => $other->id, 'title' => 'Not Mine', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->put(route('alumkit.posts.update', $post), [
            'title' => 'Hacked',
            'body' => 'Body',
        ])
        ->assertForbidden();
});

it('forbids deleting another users post', function () {
    $other = User::factory()->approved()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford']);
    $post = Post::create(['user_id' => $other->id, 'title' => 'Not Mine', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->delete(route('alumkit.posts.destroy', $post))
        ->assertForbidden();
});

it('lists only the authors own posts on the index', function () {
    $other = User::factory()->approved()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford']);

    Post::create(['user_id' => $this->user->id, 'title' => 'My Post', 'body' => 'Body']);
    Post::create(['user_id' => $other->id, 'title' => 'Their Post', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.index'))
        ->assertOk()
        ->assertSee('My Post')
        ->assertDontSee('Their Post');
});

it('forbids pending users from the posts index', function () {
    $pendingUser = User::factory()->create();
    $pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->actingAs($pendingUser)
        ->get(route('alumkit.posts.index'))
        ->assertForbidden();
});

it('forbids pending users from the create post form', function () {
    $pendingUser = User::factory()->create();
    $pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->actingAs($pendingUser)
        ->get(route('alumkit.posts.create'))
        ->assertForbidden();
});

it('renders the public posts index for guests', function () {
    Post::create([
        'user_id' => $this->user->id,
        'title' => 'Public One',
        'body' => 'Everyone can read this',
        'published_at' => now(),
    ]);

    $this->get(route('alumkit.posts.public.index'))
        ->assertOk()
        ->assertSee('Public One');
});

it('hides drafts from the public posts index', function () {
    Post::create([
        'user_id' => $this->user->id,
        'title' => 'Public One',
        'body' => 'Body',
        'published_at' => now(),
    ]);
    Post::create([
        'user_id' => $this->user->id,
        'title' => 'Secret Draft',
        'body' => 'Body',
    ]);

    $this->get(route('alumkit.posts.public.index'))
        ->assertOk()
        ->assertSee('Public One')
        ->assertDontSee('Secret Draft');
});

it('renders a published post for guests', function () {
    Post::create([
        'user_id' => $this->user->id,
        'title' => 'Read Me',
        'body' => 'Hello world body',
        'published_at' => now(),
    ]);

    $this->get(route('alumkit.posts.public.show', Post::where('title', 'Read Me')->first()))
        ->assertOk()
        ->assertSee('Hello world body');
});

it('returns 404 for draft posts on the public show route', function () {
    $post = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Hidden Draft',
        'body' => 'Body',
    ]);

    $this->get(route('alumkit.posts.public.show', $post))
        ->assertNotFound();
});
