<?php

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->tenant = Tenant::factory()->create();
    $this->admin = User::factory()->create();
    $this->admin->assignRole('tenant-admin');
    $this->tenant->users()->attach($this->admin);
});

describe('index', function () {
    it('lists only tenant users for authorized tenant admin', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/users");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Users/Index')
            ->has('users.data', 2)
            ->has('users.data.0.roles')
            ->has('users.data.1.roles')
            ->has('roles', 3)
        );
    });

    it('denies access to users without manage-users permission', function () {
        $member = User::factory()->create();
        $member->assignRole('candidate');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($member)
            ->get("/t/{$this->tenant->slug}/users");

        $response->assertForbidden();
    });

    it('allows landlord to list users', function () {
        $landlord = User::factory()->landlord()->create();

        $response = $this->actingAs($landlord)
            ->get("/t/{$this->tenant->slug}/users");

        $response->assertSuccessful();
    });
});

describe('create', function () {
    it('shows create user form for authorized user', function () {
        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/users/create");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Users/Create')
            ->has('roles', 3)
        );
    });
});

describe('store', function () {
    it('creates a new user and attaches to tenant', function () {
        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/users", [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'tenant-member',
            ]);

        $response->assertRedirect("/t/{$this->tenant->slug}/users");

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        expect($newUser->tenants)->toHaveCount(1);
        expect($newUser->hasRole('tenant-member'))->toBeTrue();
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/users", []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    });

    it('validates unique email', function () {
        $existingUser = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/users", [
                'name' => 'New User',
                'email' => 'taken@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'tenant-member',
            ]);

        $response->assertSessionHasErrors(['email']);
    });

    it('rejects the candidate role on the users route', function () {
        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/users", [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'candidate',
            ]);

        $response->assertSessionHasErrors(['role']);
    });
});

describe('edit', function () {
    it('shows edit form for an existing user', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/users/{$member->id}/edit");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Users/Edit')
            ->has('user.data')
            ->has('roles', 3)
        );
    });

    it('returns not found when editing a candidate through the users route', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/users/{$candidate->id}/edit");

        $response->assertNotFound();
    });
});

describe('update', function () {
    it('updates user name and email', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($this->admin)
            ->put("/t/{$this->tenant->slug}/users/{$member->id}", [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'tenant-manager',
            ]);

        $response->assertRedirect("/t/{$this->tenant->slug}/users");

        $member->refresh();
        expect($member->name)->toBe('Updated Name');
        expect($member->email)->toBe('updated@example.com');
        expect($member->hasRole('tenant-manager'))->toBeTrue();
    });

    it('allows updating without changing password', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $originalPassword = $member->password;

        $response = $this->actingAs($this->admin)
            ->put("/t/{$this->tenant->slug}/users/{$member->id}", [
                'name' => $member->name,
                'email' => $member->email,
                'role' => 'tenant-member',
            ]);

        $response->assertRedirect("/t/{$this->tenant->slug}/users");
    });
});

describe('destroy', function () {
    it('removes user from tenant', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($this->admin)
            ->delete("/t/{$this->tenant->slug}/users/{$member->id}");

        $response->assertRedirect("/t/{$this->tenant->slug}/users");

        expect($this->tenant->users()->where('users.id', $member->id)->exists())->toBeFalse();
    });

    it('prevents deleting yourself', function () {
        $response = $this->actingAs($this->admin)
            ->delete("/t/{$this->tenant->slug}/users/{$this->admin->id}");

        $response->assertForbidden();
    });

    it('prevents non-admin from deleting users', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($candidate)
            ->delete("/t/{$this->tenant->slug}/users/{$member->id}");

        $response->assertForbidden();
    });
});
