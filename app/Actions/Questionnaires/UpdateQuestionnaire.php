<?php

namespace App\Actions\Questionnaires;

use App\Models\Questionnaire;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnairePage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateQuestionnaire
{
    use AsAction;

    /**
     * @param  array{
     *     title?: string,
     *     description?: string|null,
     *     status?: string,
     *     settings?: array<string, mixed>|null,
     *     pages?: array<int, array{
     *         id?: int|null,
     *         title?: string|null,
     *         description?: string|null,
     *         order: int,
     *         settings?: array<string, mixed>|null,
     *         items?: array<int, array{
     *             id?: int|null,
     *             type: string,
     *             content?: string|null,
     *             required?: bool,
     *             order: int,
     *             properties?: array<string, mixed>|null,
     *             choices?: array<int, array{
     *                 id?: int|null,
     *                 content?: string|null,
     *                 is_correct?: bool,
     *                 order: int,
     *                 properties?: array<string, mixed>|null,
     *             }>
     *         }>
     *     }>
     * }  $data
     */
    public function handle(Questionnaire $questionnaire, array $data): Questionnaire
    {
        return DB::transaction(function () use ($questionnaire, $data) {
            $questionnaire->update([
                'title' => $data['title'] ?? $questionnaire->title,
                'description' => $data['description'] ?? $questionnaire->description,
                'status' => $data['status'] ?? $questionnaire->status,
                'settings' => $data['settings'] ?? $questionnaire->settings,
            ]);

            if (isset($data['pages'])) {
                $this->syncPages($questionnaire, $data['pages']);
            }

            return $questionnaire->load('pages.items.choices');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     */
    private function syncPages(Questionnaire $questionnaire, array $pages): void
    {
        $incomingPageIds = collect($pages)->pluck('id')->filter()->all();

        $questionnaire->pages()
            ->whereNotIn('id', $incomingPageIds)
            ->delete();

        foreach ($pages as $pageData) {
            $page = $questionnaire->pages()->updateOrCreate(
                ['id' => $pageData['id'] ?? null],
                [
                    'title' => $pageData['title'] ?? null,
                    'description' => $pageData['description'] ?? null,
                    'order' => $pageData['order'],
                    'settings' => $pageData['settings'] ?? null,
                ],
            );

            $this->syncItems($page, $pageData['items'] ?? []);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(QuestionnairePage $page, array $items): void
    {
        $incomingItemIds = collect($items)->pluck('id')->filter()->all();

        $page->items()
            ->whereNotIn('id', $incomingItemIds)
            ->delete();

        foreach ($items as $itemData) {
            $item = $page->items()->updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'type' => $itemData['type'],
                    'content' => $itemData['content'] ?? null,
                    'required' => $itemData['required'] ?? false,
                    'order' => $itemData['order'],
                    'properties' => $itemData['properties'] ?? null,
                ],
            );

            $this->syncChoices($item, $itemData['choices'] ?? []);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $choices
     */
    private function syncChoices(QuestionnaireItem $item, array $choices): void
    {
        $incomingChoiceIds = collect($choices)->pluck('id')->filter()->all();

        $item->choices()
            ->whereNotIn('id', $incomingChoiceIds)
            ->delete();

        foreach ($choices as $choiceData) {
            $item->choices()->updateOrCreate(
                ['id' => $choiceData['id'] ?? null],
                [
                    'content' => $choiceData['content'] ?? null,
                    'is_correct' => $choiceData['is_correct'] ?? false,
                    'order' => $choiceData['order'],
                    'properties' => $choiceData['properties'] ?? null,
                ],
            );
        }
    }
}
