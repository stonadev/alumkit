<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Alumkit\Alumkit\Notifications\MembershipRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->withProfile()->create();

    $this->plan = MembershipPlan::create([
        'name' => 'Gold',
        'price' => '100.00',
        'duration_days' => 30,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    Permission::findOrCreate('manage memberships');

    MembershipPaymentMethod::create([
        'type' => 'bank_transfer',
        'is_active' => true,
    ]);
});

it('lets a member submit a payment', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
            'proof' => UploadedFile::fake()->create('proof.pdf', 100),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('membership_payments', [
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);

    $payment = MembershipPayment::where('user_id', $this->user->id)->firstOrFail();

    expect($payment->methodLabel())->toBe('Bank Transfer');
});

it('rejects an inactive plan', function () {
    $this->plan->update(['is_active' => false]);

    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('membership_plan_id');
});

it('rejects the reserved free method', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'free',
            'paid_at' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('method');
});

it('rejects an amount that does not match the plan price', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '90.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('amount');
});

it('rejects a bad mime type', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
            'proof' => UploadedFile::fake()->create('evil.exe', 100, 'application/octet-stream'),
        ])
        ->assertSessionHasErrors('proof');
});

it('rejects a future paid_at', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.membership.payments.store'), [
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors('paid_at');
});

it('streams a proof to the owner', function () {
    Storage::fake('public');
    Storage::disk('public')->put('membership-payment-proofs/proof.pdf', 'proof-bytes');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'proof_path' => 'membership-payment-proofs/proof.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.membership.payments.proof', $payment))
        ->assertOk();
});

it('blocks another member from streaming a proof', function () {
    Storage::fake('public');
    Storage::disk('public')->put('membership-payment-proofs/proof.pdf', 'proof-bytes');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'proof_path' => 'membership-payment-proofs/proof.pdf',
        'status' => 'pending',
    ]);

    $other = User::factory()->approved()->withProfile()->create();

    $this->actingAs($other)
        ->get(route('alumkit.membership.payments.proof', $payment))
        ->assertForbidden();
});

it('lets staff stream a proof', function () {
    Storage::fake('public');
    Storage::disk('public')->put('membership-payment-proofs/proof.pdf', 'proof-bytes');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'proof_path' => 'membership-payment-proofs/proof.pdf',
        'status' => 'pending',
    ]);

    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    $this->actingAs($staff)
        ->get(route('alumkit.payments.proof', $payment))
        ->assertOk();
});

it('returns 404 when the proof file is missing', function () {
    Storage::fake('public');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'proof_path' => 'membership-payment-proofs/missing.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.membership.payments.proof', $payment))
        ->assertNotFound();
});

it('lets staff record and approve a payment directly', function () {
    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    $this->actingAs($staff)
        ->post(route('alumkit.payments.store'), [
            'user_id' => $this->user->id,
            'membership_plan_id' => $this->plan->id,
            'amount' => '100.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
            'activate' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('membership_payments', [
        'user_id' => $this->user->id,
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('memberships', [
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);
});

it('requires a reason to reject', function () {
    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($staff)
        ->post(route('alumkit.payments.reject', $payment), [])
        ->assertSessionHasErrors('review_notes');

    $this->actingAs($staff)
        ->post(route('alumkit.payments.reject', $payment), ['review_notes' => 'Proof unclear'])
        ->assertRedirect();

    $this->assertDatabaseHas('membership_payments', ['id' => $payment->id, 'status' => 'rejected']);
});

it('rejects a duplicate approval', function () {
    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $this->actingAs($staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertSessionHasErrors();

    $this->assertSame(1, $this->user->memberships()->count());
});

it('emails the member when a payment is rejected', function () {
    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    Notification::fake();

    $payment = MembershipPayment::create([
        'user_id' => $this->user->id,
        'membership_plan_id' => $this->plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($staff)
        ->post(route('alumkit.payments.reject', $payment), ['review_notes' => 'No proof'])
        ->assertRedirect();

    Notification::assertSentTo(
        $this->user,
        MembershipRejectedNotification::class,
    );
});
