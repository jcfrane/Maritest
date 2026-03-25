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
    it('lists only candidates for authorized tenant admin', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/candidates");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Candidates/Index')
            ->has('candidates.data', 1)
        );
    });
});

describe('create', function () {
    it('shows create candidate form for authorized user', function () {
        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/candidates/create");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Candidates/Create')
        );
    });
});

describe('store', function () {
    it('creates a candidate and attaches it to the tenant', function () {
        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/candidates", [
                'name' => 'New Candidate',
                'email' => 'candidate@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect("/t/{$this->tenant->slug}/candidates");

        $candidate = User::where('email', 'candidate@example.com')->first();

        expect($candidate)->not->toBeNull();
        expect($candidate->belongsToTenant($this->tenant))->toBeTrue();
        expect($candidate->hasRole('candidate'))->toBeTrue();
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->admin)
            ->post("/t/{$this->tenant->slug}/candidates", []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });
});

describe('edit', function () {
    it('shows edit form for an existing candidate', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/candidates/{$candidate->id}/edit");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Candidates/Edit')
            ->has('candidate.data')
        );
    });

    it('returns not found for tenant users on the candidates route', function () {
        $member = User::factory()->create();
        $member->assignRole('tenant-member');
        $this->tenant->users()->attach($member);

        $response = $this->actingAs($this->admin)
            ->get("/t/{$this->tenant->slug}/candidates/{$member->id}/edit");

        $response->assertNotFound();
    });

    it('uses the same centered account form layout as the create page', function () {
        $createContents = file_get_contents(resource_path('js/pages/Tenant/Candidates/Create.vue'));
        $editContents = file_get_contents(resource_path('js/pages/Tenant/Candidates/Edit.vue'));

        expect($createContents)->not->toBeFalse()
            ->and($editContents)->not->toBeFalse();

        collect([
            'panel-class="mx-auto w-full max-w-3xl"',
            'body-class="p-6 md:p-8"',
            'class="mx-auto w-full max-w-xl space-y-6"',
            '<h2',
            'Account details',
        ])->each(function (string $marker) use ($createContents, $editContents): void {
            expect($createContents)->toContain($marker);
            expect($editContents)->toContain($marker);
        });
    });
});

describe('update', function () {
    it('updates candidate details and preserves the candidate role', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $response = $this->actingAs($this->admin)
            ->put("/t/{$this->tenant->slug}/candidates/{$candidate->id}", [
                'name' => 'Updated Candidate',
                'email' => 'updated-candidate@example.com',
            ]);

        $response->assertRedirect("/t/{$this->tenant->slug}/candidates");

        $candidate->refresh();
        expect($candidate->name)->toBe('Updated Candidate');
        expect($candidate->email)->toBe('updated-candidate@example.com');
        expect($candidate->hasRole('candidate'))->toBeTrue();
    });
});

describe('destroy', function () {
    it('removes candidate from tenant', function () {
        $candidate = User::factory()->create();
        $candidate->assignRole('candidate');
        $this->tenant->users()->attach($candidate);

        $response = $this->actingAs($this->admin)
            ->delete("/t/{$this->tenant->slug}/candidates/{$candidate->id}");

        $response->assertRedirect("/t/{$this->tenant->slug}/candidates");

        expect($this->tenant->users()->where('users.id', $candidate->id)->exists())->toBeFalse();
    });
});
