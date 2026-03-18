<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Questionnaires\CreateQuestionnaire;
use App\Actions\Questionnaires\DeleteQuestionnaire;
use App\Actions\Questionnaires\ListQuestionnaires;
use App\Actions\Questionnaires\UpdateQuestionnaire;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreQuestionnaireRequest;
use App\Http\Requests\Tenant\UpdateQuestionnaireRequest;
use App\Http\Resources\QuestionnaireResource;
use App\Models\Questionnaire;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuestionnaireController extends Controller
{
    public function index(Tenant $tenant): Response
    {
        Gate::authorize('viewAny', Questionnaire::class);

        return Inertia::render('Tenant/Questionnaires/Index', [
            'questionnaires' => QuestionnaireResource::collection(
                ListQuestionnaires::run($this->getUser()),
            ),
        ]);
    }

    public function create(Tenant $tenant): Response
    {
        Gate::authorize('create', Questionnaire::class);

        return Inertia::render('Tenant/Questionnaires/Builder');
    }

    public function store(Tenant $tenant, StoreQuestionnaireRequest $request): RedirectResponse
    {
        $questionnaire = CreateQuestionnaire::run(
            $request->user()->id,
            $request->validated(),
        );

        return to_route('tenant.questionnaires.edit', [$tenant, $questionnaire])
            ->with('success', 'Questionnaire created successfully.');
    }

    public function edit(Tenant $tenant, Questionnaire $questionnaire): Response
    {
        Gate::authorize('update', $questionnaire);

        return Inertia::render('Tenant/Questionnaires/Builder', [
            'questionnaire' => json_decode((new QuestionnaireResource(
                $questionnaire->load('pages.items.choices')
            ))->toJson(), true),
        ]);
    }

    public function update(Tenant $tenant, Questionnaire $questionnaire, UpdateQuestionnaireRequest $request): RedirectResponse
    {
        UpdateQuestionnaire::run($questionnaire, $request->validated());

        return back()->with('success', 'Questionnaire saved.');
    }

    public function destroy(Tenant $tenant, Questionnaire $questionnaire): RedirectResponse
    {
        Gate::authorize('delete', $questionnaire);

        DeleteQuestionnaire::run($questionnaire);

        return to_route('tenant.questionnaires.index', $tenant)
            ->with('success', 'Questionnaire deleted successfully.');
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
