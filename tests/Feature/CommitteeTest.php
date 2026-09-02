<?php

declare(strict_types=1);

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Http\Livewire\CommitteeOrdering;
use Alumkit\Alumkit\Models\CommitteeMember;
use Alumkit\Alumkit\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->create();
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    Permission::findOrCreate('manage committee');
    $this->user->givePermissionTo('manage committee');
});

// ─── Positions ─────────────────────────────────────────────────────

it('renders the positions index', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.positions.index'))
        ->assertOk();
});

it('denies access to positions index without permission', function () {
    $other = User::factory()->approved()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->actingAs($other)
        ->get(route('alumkit.positions.index'))
        ->assertForbidden();
});

it('renders the create position form', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.positions.create'))
        ->assertOk();
});

it('creates a position', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.positions.store'), [
            'name' => 'President',
        ])
        ->assertRedirect(route('alumkit.positions.index'));

    $this->assertDatabaseHas('positions', ['name' => 'President']);
});

it('validates position name required', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.positions.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates position name unique', function () {
    Position::create(['name' => 'President']);

    $this->actingAs($this->user)
        ->post(route('alumkit.positions.store'), [
            'name' => 'President',
        ])
        ->assertSessionHasErrors(['name']);
});

it('renders the edit position form', function () {
    $position = Position::create(['name' => 'President']);

    $this->actingAs($this->user)
        ->get(route('alumkit.positions.edit', $position))
        ->assertOk()
        ->assertSee('President');
});

it('updates a position', function () {
    $position = Position::create(['name' => 'President']);

    $this->actingAs($this->user)
        ->put(route('alumkit.positions.update', $position), [
            'name' => 'Chairperson',
        ])
        ->assertRedirect(route('alumkit.positions.index'));

    $this->assertDatabaseHas('positions', ['id' => $position->id, 'name' => 'Chairperson']);
});

it('deletes a position', function () {
    $position = Position::create(['name' => 'President']);
    CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'John Doe',
        'sort_order' => 1,
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.positions.destroy', $position))
        ->assertRedirect(route('alumkit.positions.index'));

    $this->assertDatabaseMissing('positions', ['id' => $position->id]);
});

// ─── Committee Members ─────────────────────────────────────────────

it('renders the committee index', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.committee.index'))
        ->assertOk();
});

it('denies access to committee index without permission', function () {
    $other = User::factory()->approved()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->actingAs($other)
        ->get(route('alumkit.committee.index'))
        ->assertForbidden();
});

it('renders the create committee member form', function () {
    Position::create(['name' => 'President']);

    $this->actingAs($this->user)
        ->get(route('alumkit.committee.create'))
        ->assertOk();
});

it('creates a committee member with user_id', function () {
    $position = Position::create(['name' => 'President']);
    $member = User::factory()->approved()->create();

    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => $position->id,
            'user_id' => $member->id,
        ])
        ->assertRedirect(route('alumkit.committee.index'));

    $this->assertDatabaseHas('committee_members', [
        'position_id' => $position->id,
        'user_id' => $member->id,
        'sort_order' => 1,
    ]);
});

it('creates a committee member with name and photo', function () {
    Storage::fake('public');
    $position = Position::create(['name' => 'Treasurer']);

    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => $position->id,
            'name' => 'Jane Smith',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ])
        ->assertRedirect(route('alumkit.committee.index'));

    $this->assertDatabaseHas('committee_members', [
        'position_id' => $position->id,
        'name' => 'Jane Smith',
    ]);

    $member = CommitteeMember::where('name', 'Jane Smith')->first();
    expect($member->photo_path)->toContain('committee-photos/');
});

it('validates at least one of user_id or name', function () {
    $position = Position::create(['name' => 'President']);

    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => $position->id,
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates position_id exists', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => 9999,
            'name' => 'John',
        ])
        ->assertSessionHasErrors(['position_id']);
});

it('serves the committee photo through the package route', function () {
    Storage::fake('public');
    $position = Position::create(['name' => 'Treasurer']);
    $photo = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => $position->id,
            'name' => 'Jane Smith',
            'photo' => $photo,
        ]);

    $member = CommitteeMember::where('name', 'Jane Smith')->first();
    $filename = basename($member->photo_path);

    Storage::disk('public')->put('committee-photos/'.$filename, 'fake-image-content');

    $this->get(route('alumkit.committee.photo', $filename))
        ->assertOk();
});

it('renders the edit committee member form', function () {
    $position = Position::create(['name' => 'President']);
    $member = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'John Doe',
        'sort_order' => 1,
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.committee.edit', $member))
        ->assertOk()
        ->assertSee('John Doe');
});

it('updates a committee member', function () {
    $position = Position::create(['name' => 'President']);
    $otherPosition = Position::create(['name' => 'Treasurer']);
    $member = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'John Doe',
        'sort_order' => 1,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.committee.update', $member), [
            'position_id' => $otherPosition->id,
            'name' => 'John Smith',
        ])
        ->assertRedirect(route('alumkit.committee.index'));

    $this->assertDatabaseHas('committee_members', [
        'id' => $member->id,
        'position_id' => $otherPosition->id,
        'name' => 'John Smith',
    ]);
});

it('deletes a committee member and removes photo', function () {
    Storage::fake('public');
    $position = Position::create(['name' => 'President']);
    $member = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'John Doe',
        'photo_path' => 'committee-photos/photo.jpg',
        'sort_order' => 1,
    ]);

    Storage::disk('public')->put('committee-photos/photo.jpg', 'content');

    $this->actingAs($this->user)
        ->delete(route('alumkit.committee.destroy', $member))
        ->assertRedirect(route('alumkit.committee.index'));

    $this->assertDatabaseMissing('committee_members', ['id' => $member->id]);
    Storage::disk('public')->assertMissing('committee-photos/photo.jpg');
});

it('assigns incrementing sort_order on store', function () {
    $position = Position::create(['name' => 'President']);

    CommitteeMember::create(['position_id' => $position->id, 'name' => 'First', 'sort_order' => 5]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'Second', 'sort_order' => 10]);

    $this->actingAs($this->user)
        ->post(route('alumkit.committee.store'), [
            'position_id' => $position->id,
            'name' => 'Third',
        ]);

    $third = CommitteeMember::where('name', 'Third')->first();
    expect($third->sort_order)->toBe(11);
});

it('orders members by sort_order on index', function () {
    $position = Position::create(['name' => 'President']);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'Zebra', 'sort_order' => 2]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'Alpha', 'sort_order' => 1]);

    $this->actingAs($this->user)
        ->get(route('alumkit.committee.index'))
        ->assertOk()
        ->assertSeeInOrder(['Alpha', 'Zebra']);
});

// ─── Livewire Reorder ──────────────────────────────────────────────

it('reorders committee members via Livewire', function () {
    $position = Position::create(['name' => 'President']);
    $a = CommitteeMember::create(['position_id' => $position->id, 'name' => 'A', 'sort_order' => 1]);
    $b = CommitteeMember::create(['position_id' => $position->id, 'name' => 'B', 'sort_order' => 2]);
    $c = CommitteeMember::create(['position_id' => $position->id, 'name' => 'C', 'sort_order' => 3]);

    Livewire::test(CommitteeOrdering::class)
        ->call('reorder', [$c->id, $a->id, $b->id]);

    expect($a->fresh()->sort_order)->toBe(1);
    expect($b->fresh()->sort_order)->toBe(2);
    expect($c->fresh()->sort_order)->toBe(0);
});

// ─── Dashboard Integration ─────────────────────────────────────────

it('shows committee card on dashboard for users with permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Committee');
});

it('hides committee card on dashboard for users without permission', function () {
    $other = User::factory()->approved()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->actingAs($other)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertDontSee('Committee');
});

// ─── Facade API ────────────────────────────────────────────────────

it('exposes committee members through the facade API', function () {
    $position = Position::create(['name' => 'President']);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'Zebra', 'sort_order' => 2]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'Alpha', 'sort_order' => 1]);

    $members = Alumkit::committeeMembers()->get();

    expect($members->pluck('name')->all())->toBe(['Alpha', 'Zebra']);
    expect($members->first()->relationLoaded('position'))->toBeTrue();
    expect($members->first()->relationLoaded('user'))->toBeTrue();
});

it('returns limited committee members through the facade API', function () {
    $position = Position::create(['name' => 'President']);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'A', 'sort_order' => 1]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'B', 'sort_order' => 2]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'C', 'sort_order' => 3]);

    expect(Alumkit::recentCommitteeMembers(2)->pluck('name')->all())->toBe(['A', 'B']);
    expect(Alumkit::recentCommitteeMembers(0))->toHaveCount(3);
});

// ─── Position displays member count ────────────────────────────────

it('shows member count on positions index', function () {
    $position = Position::create(['name' => 'President']);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'A', 'sort_order' => 1]);
    CommitteeMember::create(['position_id' => $position->id, 'name' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->user)
        ->get(route('alumkit.positions.index'))
        ->assertOk()
        ->assertSee('2 members');
});

// ─── CommitteeMember model helpers ─────────────────────────────────

it('returns display name from user relation', function () {
    $position = Position::create(['name' => 'President']);
    $member = User::factory()->approved()->create(['name' => 'Alice']);
    $committee = CommitteeMember::create([
        'position_id' => $position->id,
        'user_id' => $member->id,
        'name' => null,
        'sort_order' => 1,
    ]);

    expect($committee->displayName())->toBe('Alice');
});

it('returns display name from name field', function () {
    $position = Position::create(['name' => 'President']);
    $committee = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'Bob Builder',
        'sort_order' => 1,
    ]);

    expect($committee->displayName())->toBe('Bob Builder');
});

it('returns photo url when photo_path is set', function () {
    $position = Position::create(['name' => 'President']);
    $committee = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'Bob',
        'photo_path' => 'committee-photos/photo.jpg',
        'sort_order' => 1,
    ]);

    expect($committee->photoUrl())->toContain('committee-photos/photo.jpg');
});

it('returns null photo url when no photo', function () {
    $position = Position::create(['name' => 'President']);
    $committee = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'Bob',
        'sort_order' => 1,
    ]);

    expect($committee->photoUrl())->toBeNull();
});
