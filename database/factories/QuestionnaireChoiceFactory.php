<?php

namespace Database\Factories;

use App\Models\QuestionnaireChoice;
use App\Models\QuestionnaireItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionnaireChoice>
 */
class QuestionnaireChoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'questionnaire_item_id' => QuestionnaireItem::factory(),
            'content' => '<p>'.fake()->sentence(3).'</p>',
            'is_correct' => false,
            'order' => 0,
            'properties' => null,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }
}
