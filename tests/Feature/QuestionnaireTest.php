<?php

use App\Models\Questionnaire;
use App\Models\QuestionnaireChoice;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnairePage;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function createLandlordWithTenant(): array
{
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['is_landlord' => true]);
    $tenant->users()->attach($user);

    return [$user, $tenant];
}

// ── Model & Relationship Tests ──────────────────────────────────────

test('questionnaire has pages relationship', function () {
    [$user] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();
    $page = QuestionnairePage::factory()
        ->for($questionnaire)
        ->create(['order' => 0]);

    expect($questionnaire->pages)->toHaveCount(1)
        ->and($questionnaire->pages->first()->id)->toBe($page->id);
});

test('questionnaire page has items relationship', function () {
    [$user] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();
    $page = QuestionnairePage::factory()->for($questionnaire)->create();
    $item = QuestionnaireItem::factory()->for($page, 'page')->create();

    expect($page->items)->toHaveCount(1)
        ->and($page->items->first()->id)->toBe($item->id);
});

test('questionnaire item has choices relationship', function () {
    [$user] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();
    $page = QuestionnairePage::factory()->for($questionnaire)->create();
    $item = QuestionnaireItem::factory()->for($page, 'page')->create();
    $choice = QuestionnaireChoice::factory()->for($item, 'item')->create();

    expect($item->choices)->toHaveCount(1)
        ->and($item->choices->first()->id)->toBe($choice->id);
});

test('questionnaire item knows if it is an instruction or question', function () {
    [$user] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();
    $page = QuestionnairePage::factory()->for($questionnaire)->create();
    $instruction = QuestionnaireItem::factory()->for($page, 'page')->instruction()->create();
    $question = QuestionnaireItem::factory()->for($page, 'page')->singleChoice()->create();

    expect($instruction->isInstruction())->toBeTrue()
        ->and($instruction->isQuestion())->toBeFalse()
        ->and($question->isQuestion())->toBeTrue()
        ->and($question->isInstruction())->toBeFalse();
});

test('questionnaire can check draft and published status', function () {
    [$user] = createLandlordWithTenant();

    $draft = Questionnaire::factory()->for($user)->create(['status' => 'draft']);
    $published = Questionnaire::factory()->for($user)->create(['status' => 'published']);

    expect($draft->isDraft())->toBeTrue()
        ->and($draft->isPublished())->toBeFalse()
        ->and($published->isPublished())->toBeTrue()
        ->and($published->isDraft())->toBeFalse();
});

// ── Controller / CRUD Tests ─────────────────────────────────────────

test('landlord can view questionnaires index', function () {
    [$user, $tenant] = createLandlordWithTenant();

    Questionnaire::factory()->for($user)->count(3)->create();

    $this->actingAs($user)
        ->get(route('tenant.questionnaires.index', $tenant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Questionnaires/Index')
            ->has('questionnaires.data', 3)
        );
});

test('landlord can view create questionnaire page', function () {
    [$user, $tenant] = createLandlordWithTenant();

    $this->actingAs($user)
        ->get(route('tenant.questionnaires.create', $tenant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Questionnaires/Builder')
        );
});

test('landlord can store a questionnaire with pages and items', function () {
    [$user, $tenant] = createLandlordWithTenant();

    $payload = [
        'title' => 'Sample Exam',
        'description' => 'A test exam',
        'status' => 'draft',
        'settings' => ['time_limit' => 60],
        'pages' => [
            [
                'title' => 'Page 1',
                'description' => 'Instructions',
                'order' => 0,
                'settings' => null,
                'items' => [
                    [
                        'type' => 'instruction',
                        'content' => '<p>Read carefully.</p>',
                        'required' => false,
                        'order' => 0,
                        'properties' => null,
                        'choices' => [],
                    ],
                    [
                        'type' => 'single_choice',
                        'content' => '<p>What is 1+1?</p>',
                        'required' => true,
                        'order' => 1,
                        'properties' => ['label_type' => 'alphabetical'],
                        'choices' => [
                            ['content' => '1', 'is_correct' => false, 'order' => 0, 'properties' => null],
                            ['content' => '2', 'is_correct' => true, 'order' => 1, 'properties' => null],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->actingAs($user)
        ->post(route('tenant.questionnaires.store', $tenant), $payload)
        ->assertRedirect();

    $questionnaire = Questionnaire::query()->latest()->first();

    expect($questionnaire)->not->toBeNull()
        ->and($questionnaire->title)->toBe('Sample Exam')
        ->and($questionnaire->pages)->toHaveCount(1)
        ->and($questionnaire->pages->first()->items)->toHaveCount(2)
        ->and($questionnaire->pages->first()->items->last()->choices)->toHaveCount(2);
});

test('landlord can update a questionnaire', function () {
    [$user, $tenant] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create(['title' => 'Old Title']);
    $page = QuestionnairePage::factory()->for($questionnaire)->create();

    $this->actingAs($user)
        ->put(route('tenant.questionnaires.update', [$tenant, $questionnaire]), [
            'title' => 'New Title',
            'description' => 'Updated',
            'status' => 'draft',
            'settings' => ['time_limit' => 30],
            'pages' => [
                [
                    'id' => $page->id,
                    'title' => 'Updated Page',
                    'description' => '',
                    'order' => 0,
                    'settings' => null,
                    'items' => [],
                ],
            ],
        ])
        ->assertRedirect();

    $questionnaire->refresh();

    expect($questionnaire->title)->toBe('New Title')
        ->and($questionnaire->pages->first()->title)->toBe('Updated Page');
});

test('landlord can delete a questionnaire', function () {
    [$user, $tenant] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('tenant.questionnaires.destroy', [$tenant, $questionnaire]))
        ->assertRedirect();

    expect(Questionnaire::query()->find($questionnaire->id))->toBeNull();
});

test('landlord can view edit questionnaire page', function () {
    [$user, $tenant] = createLandlordWithTenant();

    $questionnaire = Questionnaire::factory()->for($user)->create();
    QuestionnairePage::factory()->for($questionnaire)->create();

    $this->actingAs($user)
        ->get(route('tenant.questionnaires.edit', [$tenant, $questionnaire]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Questionnaires/Builder')
            ->has('questionnaire')
        );
});

// ── Authorization Tests ─────────────────────────────────────────────

test('unauthenticated user cannot access questionnaires', function () {
    $tenant = Tenant::factory()->create();

    $this->get(route('tenant.questionnaires.index', $tenant))
        ->assertRedirect(route('login'));
});
