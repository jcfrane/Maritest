<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('appearance settings page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance')
            ->where('auth.user.email', $user->email)
        );
});
