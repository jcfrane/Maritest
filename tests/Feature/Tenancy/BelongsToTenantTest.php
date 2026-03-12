<?php

use App\Models\Tenant;
use App\Models\User;

it('user can check if they belong to a tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();

    expect($user->belongsToTenant($tenant))->toBeFalse();

    $tenant->users()->attach($user);
    $user->refresh();

    expect($user->belongsToTenant($tenant))->toBeTrue();
});

it('user can belong to multiple tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $tenant1->users()->attach($user);
    $tenant2->users()->attach($user);

    expect($user->tenants)->toHaveCount(2)
        ->and($user->belongsToTenant($tenant1))->toBeTrue()
        ->and($user->belongsToTenant($tenant2))->toBeTrue();
});

it('landlord flag works correctly', function () {
    $user = User::factory()->create();
    $landlord = User::factory()->landlord()->create();

    expect($user->isLandlord())->toBeFalse()
        ->and($landlord->isLandlord())->toBeTrue();
});

it('tenant can be made current and retrieved', function () {
    $tenant = Tenant::factory()->create();

    expect(Tenant::current())->toBeNull();

    $tenant->makeCurrent();

    expect(Tenant::current())->not->toBeNull()
        ->and(Tenant::current()->id)->toBe($tenant->id);

    $tenant->forget();
});
