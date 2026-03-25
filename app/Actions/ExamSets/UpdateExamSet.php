<?php

namespace App\Actions\ExamSets;

use App\Models\ExamSet;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateExamSet
{
    use AsAction;

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     status: string,
     *     questionnaire_ids?: array<int, int>
     * }  $data
     */
    public function handle(ExamSet $examSet, array $data): ExamSet
    {
        return DB::transaction(function () use ($examSet, $data) {
            $examSet->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncQuestionnaires($examSet, $data['questionnaire_ids'] ?? []);

            return $examSet->load('questionnaires');
        });
    }

    /**
     * @param  array<int, int>  $questionnaireIds
     */
    private function syncQuestionnaires(ExamSet $examSet, array $questionnaireIds): void
    {
        $examSet->questionnaires()->sync(
            collect($questionnaireIds)
                ->values()
                ->mapWithKeys(fn (int $questionnaireId, int $position): array => [
                    $questionnaireId => ['position' => $position],
                ])
                ->all(),
        );
    }
}
