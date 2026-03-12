<?php

use App\Actions\Tenancy\ResolveTenantRedirect;
use App\Models\Tenant;
use App\Models\User;

it('resolves tenant from slug and grants access to tenant member', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $response = $this->actingAs($user)->get("/t/{$tenant->slug}/dashboard");

    $response->assertSuccessful();
});

it('rejects inactive tenants', function () {
    $tenant = Tenant::factory()->inactive()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $response = $this->actingAs($user)->get("/t/{$tenant->slug}/dashboard");

    $response->assertNotFound();
});

it('rejects users who do not belong to the tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get("/t/{$tenant->slug}/dashboard");

    $response->assertForbidden();
});

it('allows landlord to access any tenant', function () {
    $tenant = Tenant::factory()->create();
    $landlord = User::factory()->landlord()->create();

    $response = $this->actingAs($landlord)->get("/t/{$tenant->slug}/dashboard");

    $response->assertSuccessful();
});

it('returns 404 for nonexistent tenant', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/t/nonexistent-tenant/dashboard');

    $response->assertNotFound();
});

it('remembers the current tenant in the session after a successful tenant request', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $this->actingAs($user)
        ->get("/t/{$tenant->slug}/dashboard")
        ->assertSessionHas(ResolveTenantRedirect::SESSION_KEY, $tenant->slug);
});
