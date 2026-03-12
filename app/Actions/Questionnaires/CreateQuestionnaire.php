<?php

namespace App\Actions\Questionnaires;

use App\Models\Questionnaire;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateQuestionnaire
{
    use AsAction;

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     status?: string,
     *     settings?: array<string, mixed>|null,
     *     pages?: array<int, array{
     *         title?: string|null,
     *         description?: string|null,
     *         order: int,
     *         settings?: array<string, mixed>|null,
     *         items?: array<int, array{
     *             type: string,
     *             content?: string|null,
     *             required?: bool,
     *             order: int,
     *             properties?: array<string, mixed>|null,
     *             choices?: array<int, array{
     *                 content?: string|null,
     *                 is_correct?: bool,
     *                 order: int,
     *                 properties?: array<string, mixed>|null,
     *             }>
     *         }>
     *     }>
     * }  $data
     */
    public function handle(int $userId, array $data): Questionnaire
    {
        return DB::transaction(function () use ($userId, $data) {
            $questionnaire = Questionnaire::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'settings' => $data['settings'] ?? null,
            ]);

            $this->syncPages($questionnaire, $data['pages'] ?? []);

            return $questionnaire->load('pages.items.choices');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     */
    private function syncPages(Questionnaire $questionnaire, array $pages): void
    {
        foreach ($pages as $pageData) {
            $page = $questionnaire->pages()->create([
                'title' => $pageData['title'] ?? null,
                'description' => $pageData['description'] ?? null,
                'order' => $pageData['order'],
                'settings' => $pageData['settings'] ?? null,
            ]);

            foreach ($pageData['items'] ?? [] as $itemData) {
                $item = $page->items()->create([
                    'type' => $itemData['type'],
                    'content' => $itemData['content'] ?? null,
                    'required' => $itemData['required'] ?? false,
                    'order' => $itemData['order'],
                    'properties' => $itemData['properties'] ?? null,
                ]);

                foreach ($itemData['choices'] ?? [] as $choiceData) {
                    $item->choices()->create([
                        'content' => $choiceData['content'] ?? null,
                        'is_correct' => $choiceData['is_correct'] ?? false,
                        'order' => $choiceData['order'],
                        'properties' => $choiceData['properties'] ?? null,
                    ]);
                }
            }
        }
    }
}
