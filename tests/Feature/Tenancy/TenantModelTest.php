<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('can create a tenant with factory', function () {
    $tenant = Tenant::factory()->create();

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->name)->not->toBeEmpty()
        ->and($tenant->slug)->not->toBeEmpty()
        ->and($tenant->is_active)->toBeTrue();
});

it('enforces unique slugs', function () {
    Tenant::factory()->create(['slug' => 'test-tenant']);

    Tenant::factory()->create(['slug' => 'test-tenant']);
})->throws(UniqueConstraintViolationException::class);

it('uses slug as route key name', function () {
    $tenant = Tenant::factory()->create();

    expect($tenant->getRouteKeyName())->toBe('slug');
});

it('can have users via pivot', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();

    $tenant->users()->attach($user);

    expect($tenant->users)->toHaveCount(1)
        ->and($tenant->users->first()->id)->toBe($user->id);
});

it('can create inactive tenant', function () {
    $tenant = Tenant::factory()->inactive()->create();

    expect($tenant->is_active)->toBeFalse();
});

it('casts settings as array', function () {
    $tenant = Tenant::factory()->create([
        'settings' => ['timezone' => 'Asia/Manila'],
    ]);

    expect($tenant->settings)->toBeArray()
        ->and($tenant->settings['timezone'])->toBe('Asia/Manila');
});
