<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('allows landlords to access admin dashboard', function () {
    $landlord = User::factory()->landlord()->create();

    $response = $this->actingAs($landlord)->get('/admin/dashboard');

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Dashboard')
        ->where('auth.user.email', $landlord->email)
    );
});

it('redirects unauthenticated users from admin dashboard', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});

it('redirects tenant users from admin dashboard to their tenant dashboard', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertRedirect(route('tenant.dashboard', ['tenant' => $tenant], absolute: false));
});
