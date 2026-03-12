<?php

namespace Database\Factories;

use App\Models\Questionnaire;
use App\Models\QuestionnairePage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionnairePage>
 */
class QuestionnairePageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'questionnaire_id' => Questionnaire::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'order' => 0,
            'settings' => null,
        ];
    }
}
