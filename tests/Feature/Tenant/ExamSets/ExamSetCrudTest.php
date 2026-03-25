<?php

use App\Models\ExamSet;
use App\Models\Questionnaire;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->tenant = Tenant::factory()->create();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('tenant-admin');
    $this->tenant->users()->attach($this->admin);

    $this->manager = User::factory()->create();
    $this->manager->assignRole('tenant-manager');
    $this->tenant->users()->attach($this->manager);

    $this->member = User::factory()->create();
    $this->member->assignRole('tenant-member');
    $this->tenant->users()->attach($this->member);
});

function createPublishedQuestionnaire(Tenant $tenant, User $user, array $attributes = []): Questionnaire
{
    return Questionnaire::factory()
        ->for($tenant)
        ->for($user)
        ->published()
        ->create($attributes);
}

test('exam set has tenant user and ordered questionnaire relationships', function () {
    $questionnaireA = createPublishedQuestionnaire($this->tenant, $this->admin);
    $questionnaireB = createPublishedQuestionnaire($this->tenant, $this->admin);

    $examSet = ExamSet::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create();

    $examSet->questionnaires()->attach([
        $questionnaireB->id => ['position' => 0],
        $questionnaireA->id => ['position' => 1],
    ]);

    $examSet->load('tenant', 'user', 'questionnaires');

    expect($examSet->tenant->is($this->tenant))->toBeTrue()
        ->and($examSet->user->is($this->admin))->toBeTrue()
        ->and($examSet->questionnaires->pluck('id')->all())
        ->toBe([$questionnaireB->id, $questionnaireA->id]);
});

test('tenant admin can view exam sets index', function () {
    $questionnaire = createPublishedQuestionnaire($this->tenant, $this->admin);

    $examSet = ExamSet::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create(['title' => 'Hiring Bundle']);

    $examSet->questionnaires()->attach($questionnaire->id, ['position' => 0]);

    $this->actingAs($this->admin)
        ->get(route('tenant.exam-sets.index', $this->tenant))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/ExamSets/Index')
            ->has('examSets.data', 1)
            ->where('examSets.data.0.title', 'Hiring Bundle')
            ->where('examSets.data.0.questionnaire_count', 1)
        );
});

test('tenant manager can view create exam set page with published questionnaires only', function () {
    createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Published Q']);

    Questionnaire::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create(['title' => 'Draft Q']);

    $this->actingAs($this->manager)
        ->get(route('tenant.exam-sets.create', $this->tenant))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/ExamSets/Create')
            ->has('availableQuestionnaires', 1)
            ->where('availableQuestionnaires.0.title', 'Published Q')
        );
});

test('tenant admin can create a published exam set with ordered questionnaires', function () {
    $questionnaireA = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire A']);
    $questionnaireB = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire B']);

    $this->actingAs($this->admin)
        ->post(route('tenant.exam-sets.store', $this->tenant), [
            'title' => 'Operations Set',
            'description' => 'Ready for launch',
            'status' => 'published',
            'questionnaire_ids' => [$questionnaireB->id, $questionnaireA->id],
        ])
        ->assertRedirect(route('tenant.exam-sets.index', $this->tenant));

    $examSet = ExamSet::query()
        ->with('questionnaires')
        ->latest()
        ->first();

    expect($examSet)->not->toBeNull()
        ->and($examSet->title)->toBe('Operations Set')
        ->and($examSet->status)->toBe('published')
        ->and($examSet->questionnaires->pluck('id')->all())
        ->toBe([$questionnaireB->id, $questionnaireA->id]);
});

test('tenant admin can view edit exam set page', function () {
    $questionnaireA = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire A']);
    $questionnaireB = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire B']);

    $examSet = ExamSet::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create(['title' => 'Customer Service Set']);

    $examSet->questionnaires()->attach([
        $questionnaireA->id => ['position' => 0],
        $questionnaireB->id => ['position' => 1],
    ]);

    $this->actingAs($this->admin)
        ->get(route('tenant.exam-sets.edit', [$this->tenant, $examSet]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/ExamSets/Edit')
            ->where('examSet.data.title', 'Customer Service Set')
            ->has('examSet.data.questionnaires', 2)
            ->where('examSet.data.questionnaires.0.title', 'Questionnaire A')
        );
});

test('tenant admin can update exam set details and questionnaire order', function () {
    $questionnaireA = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire A']);
    $questionnaireB = createPublishedQuestionnaire($this->tenant, $this->admin, ['title' => 'Questionnaire B']);

    $examSet = ExamSet::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create(['title' => 'Old Set']);

    $examSet->questionnaires()->attach([
        $questionnaireA->id => ['position' => 0],
        $questionnaireB->id => ['position' => 1],
    ]);

    $this->actingAs($this->admin)
        ->put(route('tenant.exam-sets.update', [$this->tenant, $examSet]), [
            'title' => 'Updated Set',
            'description' => 'Updated description',
            'status' => 'draft',
            'questionnaire_ids' => [$questionnaireB->id, $questionnaireA->id],
        ])
        ->assertRedirect(route('tenant.exam-sets.index', $this->tenant));

    $examSet->refresh()->load('questionnaires');

    expect($examSet->title)->toBe('Updated Set')
        ->and($examSet->questionnaires->pluck('id')->all())
        ->toBe([$questionnaireB->id, $questionnaireA->id]);
});

test('deleting an exam set does not delete questionnaires', function () {
    $questionnaire = createPublishedQuestionnaire($this->tenant, $this->admin);

    $examSet = ExamSet::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create();

    $examSet->questionnaires()->attach($questionnaire->id, ['position' => 0]);

    $this->actingAs($this->admin)
        ->delete(route('tenant.exam-sets.destroy', [$this->tenant, $examSet]))
        ->assertRedirect(route('tenant.exam-sets.index', $this->tenant));

    expect(ExamSet::query()->find($examSet->id))->toBeNull()
        ->and(Questionnaire::query()->find($questionnaire->id))->not->toBeNull();
});

test('tenant member cannot access exam set management', function () {
    $this->actingAs($this->member)
        ->get(route('tenant.exam-sets.index', $this->tenant))
        ->assertForbidden();
});

test('landlord can access exam set management without tenant membership', function () {
    $landlord = User::factory()->landlord()->create();

    $this->actingAs($landlord)
        ->get(route('tenant.exam-sets.index', $this->tenant))
        ->assertSuccessful();
});

test('published exam sets require at least one questionnaire', function () {
    $this->actingAs($this->admin)
        ->post(route('tenant.exam-sets.store', $this->tenant), [
            'title' => 'Incomplete Set',
            'description' => 'Missing questionnaire',
            'status' => 'published',
            'questionnaire_ids' => [],
        ])
        ->assertSessionHasErrors('questionnaire_ids');
});

test('draft exam sets can be saved without questionnaires', function () {
    $this->actingAs($this->admin)
        ->post(route('tenant.exam-sets.store', $this->tenant), [
            'title' => 'Draft Set',
            'description' => 'No questionnaires yet',
            'status' => 'draft',
            'questionnaire_ids' => [],
        ])
        ->assertRedirect(route('tenant.exam-sets.index', $this->tenant));

    $examSet = ExamSet::query()->latest()->first();

    expect($examSet)->not->toBeNull()
        ->and($examSet->questionnaires)->toHaveCount(0);
});

test('questionnaires must belong to the current tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->assignRole('tenant-admin');
    $otherTenant->users()->attach($otherUser);

    $foreignQuestionnaire = createPublishedQuestionnaire($otherTenant, $otherUser);

    $this->actingAs($this->admin)
        ->post(route('tenant.exam-sets.store', $this->tenant), [
            'title' => 'Cross Tenant Set',
            'description' => 'Invalid questionnaire source',
            'status' => 'published',
            'questionnaire_ids' => [$foreignQuestionnaire->id],
        ])
        ->assertSessionHasErrors('questionnaire_ids.0');
});

test('only published questionnaires may be attached to an exam set', function () {
    $draftQuestionnaire = Questionnaire::factory()
        ->for($this->tenant)
        ->for($this->admin)
        ->create();

    $this->actingAs($this->admin)
        ->post(route('tenant.exam-sets.store', $this->tenant), [
            'title' => 'Draft Questionnaire Set',
            'description' => 'Invalid questionnaire status',
            'status' => 'draft',
            'questionnaire_ids' => [$draftQuestionnaire->id],
        ])
        ->assertSessionHasErrors('questionnaire_ids.0');
});
