<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ListCandidates;
use App\Actions\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCandidateRequest;
use App\Http\Requests\Tenant\UpdateCandidateRequest;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function index(Tenant $tenant): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Tenant/Candidates/Index', [
            'candidates' => UserResource::collection(ListCandidates::run($tenant)),
        ]);
    }

    public function create(Tenant $tenant): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Tenant/Candidates/Create');
    }

    public function store(Tenant $tenant, StoreCandidateRequest $request): RedirectResponse
    {
        CreateUser::run($tenant, [
            ...$request->validated(),
            'role' => 'candidate',
        ]);

        return to_route('tenant.candidates.index', $tenant)
            ->with('success', 'Candidate created successfully.');
    }

    public function edit(Tenant $tenant, User $candidate): Response
    {
        $this->ensureCandidateBelongsToTenant($tenant, $candidate);

        Gate::authorize('update', $candidate);

        return Inertia::render('Tenant/Candidates/Edit', [
            'candidate' => new UserResource($candidate->load('roles')),
        ]);
    }

    public function update(Tenant $tenant, User $candidate, UpdateCandidateRequest $request): RedirectResponse
    {
        $this->ensureCandidateBelongsToTenant($tenant, $candidate);

        UpdateUser::run($candidate, [
            ...$request->validated(),
            'role' => 'candidate',
        ]);

        return to_route('tenant.candidates.index', $tenant)
            ->with('success', 'Candidate updated successfully.');
    }

    public function destroy(Tenant $tenant, User $candidate): RedirectResponse
    {
        $this->ensureCandidateBelongsToTenant($tenant, $candidate);

        Gate::authorize('delete', $candidate);

        DeleteUser::run($tenant, $candidate);

        return to_route('tenant.candidates.index', $tenant)
            ->with('success', 'Candidate removed successfully.');
    }

    private function ensureCandidateBelongsToTenant(Tenant $tenant, User $candidate): void
    {
        abort_unless(
            $candidate->belongsToTenant($tenant) && $candidate->hasRole('candidate'),
            404,
        );
    }
}
