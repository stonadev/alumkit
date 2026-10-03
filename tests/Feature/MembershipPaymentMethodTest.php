<?php

declare(strict_types=1);

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);

    Permission::findOrCreate('manage membership plans');
    Permission::findOrCreate('manage memberships');

    $this->user = User::factory()->approved()->withProfile()->create();
    $this->user->givePermissionTo('manage membership plans');
});

function makeMethod(array $overrides = []): MembershipPaymentMethod
{
    return MembershipPaymentMethod::create(array_merge([
        'type' => 'bkash',
        'instructions' => 'Send money to 01700000000.',
        'is_active' => true,
    ], $overrides));
}

it('lets staff create a payment method', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.payment-methods.store'), [
            'type' => 'bkash',
            'instructions' => '<p>Send Money to <strong>01811111111</strong>.</p>',
            'is_active' => '1',
        ])
        ->assertRedirect(route('alumkit.payment-methods.index'));

    $this->assertDatabaseHas('membership_payment_methods', [
        'type' => 'bkash',
        'instructions' => '<p>Send Money to <strong>01811111111</strong>.</p>',
        'is_active' => true,
    ]);
});

it('requires a type', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.payment-methods.store'), [
            'instructions' => 'Send money to 01700000000.',
        ])
        ->assertSessionHasErrors('type');
});

it('rejects an unsupported method type', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.payment-methods.store'), [
            'type' => 'cash',
        ])
        ->assertSessionHasErrors('type');
});

it('rejects a duplicate method type', function () {
    makeMethod(['type' => 'bkash']);

    $this->actingAs($this->user)
        ->post(route('alumkit.payment-methods.store'), [
            'type' => 'bkash',
        ])
        ->assertSessionHasErrors('type');
});

it('accepts a method without instructions', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.payment-methods.store'), [
            'type' => 'bkash',
        ])
        ->assertRedirect(route('alumkit.payment-methods.index'));

    $this->assertDatabaseHas('membership_payment_methods', [
        'type' => 'bkash',
        'instructions' => null,
    ]);
});

it('lets staff update a method', function () {
    $method = makeMethod();

    $this->actingAs($this->user)
        ->put(route('alumkit.payment-methods.update', $method), [
            'instructions' => 'Updated instructions.',
            'is_active' => '0',
        ])
        ->assertRedirect(route('alumkit.payment-methods.index'));

    $method->refresh();

    expect($method->type)->toBe('bkash')
        ->and($method->instructions)->toBe('Updated instructions.')
        ->and($method->is_active)->toBeFalse();
});

it('locks the type on update', function () {
    $method = makeMethod(['type' => 'bkash']);

    $this->actingAs($this->user)
        ->put(route('alumkit.payment-methods.update', $method), [
            'type' => 'bank_transfer',
            'instructions' => 'Updated instructions.',
        ])
        ->assertSessionHasErrors('type');

    expect($method->refresh()->type)->toBe('bkash');
});

it('lets staff delete a method', function () {
    $method = makeMethod();

    $this->actingAs($this->user)
        ->delete(route('alumkit.payment-methods.destroy', $method))
        ->assertRedirect(route('alumkit.payment-methods.index'));

    $this->assertDatabaseMissing('membership_payment_methods', ['id' => $method->getKey()]);
});

it('renders the methods index', function () {
    makeMethod(['type' => 'bkash', 'instructions' => 'Send money to 01700000000.']);
    makeMethod(['type' => 'bank_transfer', 'instructions' => 'Transfer to account 1234567890.']);

    $this->actingAs($this->user)
        ->get(route('alumkit.payment-methods.index'))
        ->assertOk()
        ->assertSee('bKash')
        ->assertSee('Bank Transfer');
});

it('reorders methods via drag and drop', function () {
    $a = makeMethod(['type' => 'bkash']);
    $b = makeMethod(['type' => 'nagad']);
    $c = makeMethod(['type' => 'bank_transfer']);

    $this->actingAs($this->user)
        ->postJson(route('alumkit.payment-methods.reorder'), ['ids' => [$c->id, $a->id, $b->id]])
        ->assertOk();

    expect($c->fresh()->sort_order)->toBe(0)
        ->and($a->fresh()->sort_order)->toBe(1)
        ->and($b->fresh()->sort_order)->toBe(2);
});

it('denies reordering methods without permission', function () {
    $other = User::factory()->approved()->withProfile()->create();

    $this->actingAs($other)
        ->postJson(route('alumkit.payment-methods.reorder'), ['ids' => []])
        ->assertForbidden();
});

it('denies the methods index without permission', function () {
    $other = User::factory()->approved()->withProfile()->create();

    $this->actingAs($other)
        ->get(route('alumkit.payment-methods.index'))
        ->assertForbidden();
});

it('lists active payment methods on the facade', function () {
    makeMethod(['type' => 'bkash']);
    makeMethod(['type' => 'bank_transfer']);

    $methods = Alumkit::paymentMethods();

    expect($methods)->toHaveKey('bkash')
        ->toHaveKey('bank_transfer')
        ->not->toHaveKey('free');
});

it('shows active methods on the member payment form', function () {
    makeMethod(['type' => 'bkash', 'instructions' => 'Send money to 01700000000.']);
    makeMethod(['type' => 'bank_transfer']);

    $member = User::factory()->approved()->withProfile()->create();

    $this->actingAs($member)
        ->get(route('alumkit.membership.payments.create'))
        ->assertOk()
        ->assertSee('bKash')
        ->assertSee('Bank Transfer');
});

it('renders member payment form instructions as HTML from editor JSON', function () {
    makeMethod([
        'type' => 'bkash',
        'instructions' => json_encode([
            'time' => 1791035583729,
            'blocks' => [
                ['id' => 'oGpYyW2yGK', 'type' => 'paragraph', 'data' => ['text' => 'Send money to 01811111111.']],
            ],
            'version' => '2.30.0',
        ]),
    ]);

    $member = User::factory()->approved()->withProfile()->create();

    $this->actingAs($member)
        ->get(route('alumkit.membership.payments.create'))
        ->assertOk()
        ->assertSee('<p>Send money to 01811111111.</p>', false);
});
