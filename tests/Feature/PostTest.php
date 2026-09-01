<?php

declare(strict_types=1);

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->create();
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);
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
        ->assertOk()
        ->assertSee('data-alumkit-editor');
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

it('creates a post with a thumbnail', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('alumkit.posts.store'), [
            'title' => 'Thumbnailed Post',
            'body' => 'Has an image',
            'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
        ])
        ->assertRedirect(route('alumkit.posts.index'));

    $post = Post::where('title', 'Thumbnailed Post')->first();

    expect($post->thumbnail)->not->toBeNull();
    Storage::disk('public')->assertExists('post-thumbnails/'.basename($post->thumbnail));
});

it('serves the post thumbnail through the package route', function () {
    Storage::fake('public');

    $post = Post::create([
        'user_id' => $this->user->id,
        'title' => 'With Thumbnail',
        'body' => 'Body',
        'thumbnail' => 'post-thumbnails/thumb.jpg',
    ]);

    Storage::disk('public')->put('post-thumbnails/thumb.jpg', 'fake-image');

    $this->get(route('alumkit.posts.thumbnail', basename($post->thumbnail)))->assertOk();
});

it('validates thumbnail must be an image', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('alumkit.posts.store'), [
            'title' => 'Bad Thumbnail',
            'body' => 'Body',
            'thumbnail' => UploadedFile::fake()->create('doc.txt', 1),
        ])
        ->assertSessionHasErrors(['thumbnail']);
});

it('updates a post thumbnail on edit', function () {
    Storage::fake('public');

    $post = Post::create(['user_id' => $this->user->id, 'title' => 'Old', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->put(route('alumkit.posts.update', $post), [
            'title' => 'Old',
            'body' => 'Body',
            'thumbnail' => UploadedFile::fake()->image('new-thumb.jpg'),
        ])
        ->assertRedirect(route('alumkit.posts.index'));

    $fresh = $post->fresh();

    expect($fresh->thumbnail)->not->toBeNull();
    Storage::disk('public')->assertExists('post-thumbnails/'.basename($fresh->thumbnail));
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
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford', 'subject' => 'Computer Science', 'start_year' => 2015]);
    $post = Post::create(['user_id' => $other->id, 'title' => 'Not Mine', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.edit', $post))
        ->assertForbidden();
});

it('forbids updating another users post', function () {
    $other = User::factory()->approved()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford', 'subject' => 'Computer Science', 'start_year' => 2015]);
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
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford', 'subject' => 'Computer Science', 'start_year' => 2015]);
    $post = Post::create(['user_id' => $other->id, 'title' => 'Not Mine', 'body' => 'Body']);

    $this->actingAs($this->user)
        ->delete(route('alumkit.posts.destroy', $post))
        ->assertForbidden();
});

it('lists only the authors own posts on the index', function () {
    $other = User::factory()->approved()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'Stanford', 'subject' => 'Computer Science', 'start_year' => 2015]);

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
    $pendingUser->profile()->create();
    $pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->actingAs($pendingUser)
        ->get(route('alumkit.posts.index'))
        ->assertForbidden();
});

it('forbids pending users from the create post form', function () {
    $pendingUser = User::factory()->create();
    $pendingUser->profile()->create();
    $pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->actingAs($pendingUser)
        ->get(route('alumkit.posts.create'))
        ->assertForbidden();
});

it('does not register public blog routes', function () {
    expect(Route::has('alumkit.posts.public.index'))->toBeFalse();
    expect(Route::has('alumkit.posts.public.show'))->toBeFalse();
});

it('exposes published posts through the facade API', function () {
    Post::create(['user_id' => $this->user->id, 'title' => 'Old Draft', 'body' => 'x']);
    $older = Post::create(['user_id' => $this->user->id, 'title' => 'Older Published', 'body' => 'x', 'published_at' => now()->subDay()]);
    $newer = Post::create(['user_id' => $this->user->id, 'title' => 'Newest Published', 'body' => 'x', 'published_at' => now()]);

    // Distinct created_at, since SQLite stores timestamps at second precision and same-second inserts tie.
    $older->forceFill(['created_at' => now()->subDays(2)])->save();
    $newer->forceFill(['created_at' => now()->subDay()])->save();

    $posts = Alumkit::publishedPosts()->get();

    expect($posts->pluck('title')->all())->toBe(['Newest Published', 'Older Published']);
    expect($posts->first()->relationLoaded('user'))->toBeTrue();
});

it('returns the most recent published posts through the facade API', function () {
    foreach (['P1', 'P2', 'P3', 'P4'] as $i => $title) {
        $post = Post::create(['user_id' => $this->user->id, 'title' => $title, 'body' => 'x', 'published_at' => now()->subHours(4 - $i)]);

        // Distinct created_at, since SQLite stores timestamps at second precision and same-second inserts tie.
        $post->forceFill(['created_at' => now()->subHours(4 - $i)])->save();
    }

    expect(Alumkit::recentPosts(2)->pluck('title')->all())->toBe(['P4', 'P3']);
    expect(Alumkit::recentPosts(0))->toBeEmpty();
});
