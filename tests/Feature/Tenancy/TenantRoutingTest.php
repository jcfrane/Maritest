<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects unauthenticated users from tenant routes', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->get("/t/{$tenant->slug}/dashboard");

    $response->assertRedirect('/login');
});

it('redirects unauthenticated users from admin routes', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});

it('loads the tenant dashboard for authorized user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $response = $this->actingAs($user)->get("/t/{$tenant->slug}/dashboard");

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Tenant/Dashboard')
        ->where('currentTenant.slug', $tenant->slug)
    );
});
