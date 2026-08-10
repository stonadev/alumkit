<?php

declare(strict_types=1);

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

// NOTE: no Event::fake() here — the Registered listener that dispatches the
// verification mail must run for this test to exercise the real flow.
beforeEach(function () {
    Notification::fake();
});

it('sends the verification email on registration', function () {
    $this->post(route('register'), [
        'name' => 'Mail Test',
        'email' => 'mailtest@example.com',
        'phone' => '+1234567890',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertRedirect();

    Notification::assertSentTo(
        User::where('email', 'mailtest@example.com')->first(),
        VerifyEmail::class,
    );
});
