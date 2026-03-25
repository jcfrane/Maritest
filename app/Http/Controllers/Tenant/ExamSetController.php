<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ExamSets\CreateExamSet;
use App\Actions\ExamSets\DeleteExamSet;
use App\Actions\ExamSets\ListExamSets;
use App\Actions\ExamSets\UpdateExamSet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreExamSetRequest;
use App\Http\Requests\Tenant\UpdateExamSetRequest;
use App\Http\Resources\ExamSetResource;
use App\Models\ExamSet;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ExamSetController extends Controller
{
    public function index(Tenant $tenant): Response
    {
        Gate::authorize('viewAny', ExamSet::class);

        return Inertia::render('Tenant/ExamSets/Index', [
            'examSets' => ExamSetResource::collection(
                ListExamSets::run($tenant),
            ),
        ]);
    }

    public function create(Tenant $tenant): Response
    {
        Gate::authorize('create', ExamSet::class);

        return Inertia::render('Tenant/ExamSets/Create', [
            'availableQuestionnaires' => $this->availableQuestionnaires($tenant),
        ]);
    }

    public function store(Tenant $tenant, StoreExamSetRequest $request): RedirectResponse
    {
        CreateExamSet::run($tenant, $request->user(), $request->validated());

        return to_route('tenant.exam-sets.index', $tenant)
            ->with('success', 'Exam set created successfully.');
    }

    public function edit(Tenant $tenant, ExamSet $examSet): Response
    {
        Gate::authorize('update', $examSet);

        return Inertia::render('Tenant/ExamSets/Edit', [
            'examSet' => new ExamSetResource(
                $examSet->load('questionnaires'),
            ),
            'availableQuestionnaires' => $this->availableQuestionnaires($tenant),
        ]);
    }

    public function update(
        Tenant $tenant,
        ExamSet $examSet,
        UpdateExamSetRequest $request,
    ): RedirectResponse {
        UpdateExamSet::run($examSet, $request->validated());

        return to_route('tenant.exam-sets.index', $tenant)
            ->with('success', 'Exam set updated successfully.');
    }

    public function destroy(Tenant $tenant, ExamSet $examSet): RedirectResponse
    {
        Gate::authorize('delete', $examSet);

        DeleteExamSet::run($examSet);

        return to_route('tenant.exam-sets.index', $tenant)
            ->with('success', 'Exam set deleted successfully.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function availableQuestionnaires(Tenant $tenant): array
    {
        return $tenant->questionnaires()
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'description', 'status', 'updated_at'])
            ->map(fn ($questionnaire): array => [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'description' => $questionnaire->description,
                'status' => $questionnaire->status,
                'updated_at' => $questionnaire->updated_at,
            ])
            ->values()
            ->all();
    }
}
