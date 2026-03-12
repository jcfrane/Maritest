<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('tenant members can view the tenant dashboard', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $this->actingAs($user)
        ->get("/t/{$tenant->slug}/dashboard")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Dashboard')
            ->where('auth.user.email', $user->email)
            ->where('currentTenant.slug', $tenant->slug)
        );
});

test('guests are redirected from the tenant dashboard', function () {
    $tenant = Tenant::factory()->create();

    $this->get("/t/{$tenant->slug}/dashboard")
        ->assertRedirect(route('login'));
});
