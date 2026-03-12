<?php

use App\Actions\Tenancy\ResolveTenantRedirect;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyFeature(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});

test('tenant users are redirected to the remembered tenant dashboard after login', function () {
    $firstTenant = Tenant::factory()->create();
    $rememberedTenant = Tenant::factory()->create();
    $user = User::factory()->create();

    $firstTenant->users()->attach($user);
    $rememberedTenant->users()->attach($user);

    $response = $this
        ->withSession([ResolveTenantRedirect::SESSION_KEY => $rememberedTenant->slug])
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('tenant.dashboard', ['tenant' => $rememberedTenant], absolute: false));
});

test('tenant login falls back to the first active attached tenant when the remembered tenant is stale', function () {
    $fallbackTenant = Tenant::factory()->create();
    $secondTenant = Tenant::factory()->create();
    $inactiveTenant = Tenant::factory()->inactive()->create();
    $user = User::factory()->create();

    $fallbackTenant->users()->attach($user);
    $secondTenant->users()->attach($user);
    $inactiveTenant->users()->attach($user);

    $response = $this
        ->withSession([ResolveTenantRedirect::SESSION_KEY => $inactiveTenant->slug])
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('tenant.dashboard', ['tenant' => $fallbackTenant], absolute: false));
});
