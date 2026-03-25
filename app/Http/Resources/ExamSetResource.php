<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'questionnaires' => $this->whenLoaded('questionnaires', function (): array {
                return $this->questionnaires
                    ->map(fn ($questionnaire): array => [
                        'id' => $questionnaire->id,
                        'title' => $questionnaire->title,
                        'description' => $questionnaire->description,
                        'status' => $questionnaire->status,
                        'position' => $questionnaire->pivot?->position,
                    ])
                    ->values()
                    ->all();
            }),
            'questionnaire_count' => $this->whenLoaded('questionnaires', fn (): int => $this->questionnaires->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
