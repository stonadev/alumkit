<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Event::fake();
});

it('renders the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.sign_in'));
});

it('renders the password field masked with a reveal toggle', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('name="password"', false)
        ->assertSee('type="password"', false)
        ->assertSee('alumkit_form_password_reveal', false)
        ->assertSee("show ? 'text' : 'password'", false);
});

it('renders the registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.register'));
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('alumkit.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('logs in a rejected user', function () {
    $user = User::factory()->create(['state' => 'rejected']);
    $user->profile()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('alumkit.dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('alumkit.dashboard'))->assertOk();
});

it('logs in a suspended user', function () {
    $user = User::factory()->create(['state' => 'suspended']);
    $user->profile()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('alumkit.dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::dashboard.account_suspended'));
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors();

    $this->assertGuest();
});

it('validates login required fields', function () {
    $this->post(route('login'), [
        'email' => '',
        'password' => '',
    ])->assertSessionHasErrors(['email', 'password']);
});

it('logs out and invalidates session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();

    $this->assertGuest();
});

it('renders the dashboard for authenticated users', function () {
    $user = User::factory()->create();
    $user->profile()->create();
    $user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'start_year' => 2015]);
    $user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->actingAs($user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.dashboard'))
        ->assertSee($user->email);
});

it('redirects unauthenticated users to login', function () {
    $this->get(route('alumkit.dashboard'))
        ->assertRedirect(route('login'));
});

it('registers a new user', function () {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
    ]);

    Event::assertDispatched(Registered::class);
});

it('validates registration fields', function () {
    $this->post(route('register'), [
        'name' => '',
        'email' => '',
        'phone' => '',
        'password' => '',
        'password_confirmation' => '',
    ])->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
});

it('validates unique email on registration', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertSessionHasErrors(['email']);
});

it('renders the forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.forgot_password'));
});

it('sends password reset link', function () {
    $user = User::factory()->create();

    $this->post(route('password.email'), [
        'email' => $user->email,
    ])->assertSessionHasNoErrors()
        ->assertSessionHas('status');
});

it('rejects password reset with unknown email', function () {
    $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ])->assertSessionHasErrors(['email']);
});

it('renders the reset password page', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertSee(__('alumkit::auth.reset_password'));
});

it('resets password with valid token', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);
    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertCredentials([
        'email' => $user->email,
        'password' => 'new-password',
    ]);
});

it('renders the confirm password page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('password.confirm'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.confirm_password_title'));
});

it('redirects unverified users from dashboard', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('alumkit.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('renders the profile page for verified users', function () {
    $user = User::factory()->create();
    $user->profile()->create();
    $user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'start_year' => 2015]);
    $user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->actingAs($user)
        ->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee(__('alumkit::auth.profile'))
        ->assertSee(__('alumkit::auth.logout'));
});

it('updates user profile information', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-profile-information.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('updates user password', function () {
    $user = User::factory()->create([
        'password' => 'current-password',
    ]);

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

    $this->assertCredentials([
        'email' => $user->email,
        'password' => 'new-password',
    ]);
});

it('rejects password update with wrong current password', function () {
    $user = User::factory()->create([
        'password' => 'current-password',
    ]);

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors(['current_password']);
});

it('redirects authenticated users from login to dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('alumkit.dashboard'));
});
