<?php

namespace Database\Factories;

use App\Models\QuestionnaireItem;
use App\Models\QuestionnairePage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionnaireItem>
 */
class QuestionnaireItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'questionnaire_page_id' => QuestionnairePage::factory(),
            'type' => fake()->randomElement(['short_text', 'long_text', 'single_choice', 'multiple_choice']),
            'content' => '<p>'.fake()->sentence().'</p>',
            'required' => fake()->boolean(70),
            'order' => 0,
            'properties' => null,
        ];
    }

    public function instruction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'instruction',
            'required' => false,
        ]);
    }

    public function singleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'single_choice',
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple_choice',
        ]);
    }

    public function fileUpload(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'file_upload',
            'properties' => [
                'allowed_extensions' => ['pdf', 'jpg', 'png'],
                'max_file_size' => 5120,
            ],
        ]);
    }
}
