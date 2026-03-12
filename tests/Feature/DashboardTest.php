<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('non tenant users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('auth.user.email', $user->email)
        ->has('roles')
        ->has('permissions')
    );
});

test('tenant users are redirected from the dashboard to their tenant dashboard', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('tenant.dashboard', ['tenant' => $tenant], absolute: false));
});
