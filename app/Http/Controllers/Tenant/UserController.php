<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ListUsers;
use App\Actions\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Tenant $tenant): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = ListUsers::run($tenant);

        return Inertia::render('Tenant/Users/Index', [
            'users' => UserResource::collection($users),
            'roles' => $this->availableRoles(),
        ]);
    }

    public function create(Tenant $tenant): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Tenant/Users/Create', [
            'roles' => $this->availableRoles(),
        ]);
    }

    public function store(Tenant $tenant, StoreUserRequest $request): RedirectResponse
    {
        CreateUser::run($tenant, $request->validated());

        return to_route('tenant.users.index', $tenant)
            ->with('success', 'User created successfully.');
    }

    public function edit(Tenant $tenant, User $user): Response
    {
        $this->ensureIsTenantUser($tenant, $user);
        abort_if($user->hasRole('candidate'), 404);

        Gate::authorize('update', $user);

        return Inertia::render('Tenant/Users/Edit', [
            'user' => new UserResource($user->load('roles')),
            'roles' => $this->availableRoles(),
        ]);
    }

    public function update(Tenant $tenant, User $user, UpdateUserRequest $request): RedirectResponse
    {
        $this->ensureIsTenantUser($tenant, $user);
        abort_if($user->hasRole('candidate'), 404);

        UpdateUser::run($user, $request->validated());

        return to_route('tenant.users.index', $tenant)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Tenant $tenant, User $user): RedirectResponse
    {
        $this->ensureIsTenantUser($tenant, $user);
        abort_if($user->hasRole('candidate'), 404);

        Gate::authorize('delete', $user);

        DeleteUser::run($tenant, $user);

        return to_route('tenant.users.index', $tenant)
            ->with('success', 'User removed successfully.');
    }

    /**
     * @return Collection<int, string>
     */
    private function availableRoles(): Collection
    {
        return Role::whereIn('name', [
            'tenant-admin',
            'tenant-manager',
            'tenant-member',
        ])->pluck('name');
    }

    private function ensureIsTenantUser(Tenant $tenant, User $user): void
    {
        abort_unless($user->belongsToTenant($tenant), 404);
    }
}
