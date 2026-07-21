<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('renders the login page', function () {
    $this->get(route('alumkit.login'))
        ->assertOk()
        ->assertSee('Sign in');
});

it('redirects unauthenticated users to login', function () {
    $this->get(route('alumkit.dashboard'))
        ->assertRedirect(route('alumkit.login'));
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->post(route('alumkit.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('alumkit.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->post(route('alumkit.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect();

    $this->assertGuest();
});

it('validates required fields', function () {
    $this->post(route('alumkit.login'), [
        'email' => '',
        'password' => '',
    ])->assertSessionHasErrors(['email', 'password']);
});

it('logs out and invalidates session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('alumkit.logout'))
        ->assertRedirect(route('alumkit.login'));

    $this->assertGuest();
});

it('renders the dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee($user->name);
});
