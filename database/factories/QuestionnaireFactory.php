<?php

namespace Database\Factories;

use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Questionnaire>
 */
class QuestionnaireFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => 'draft',
            'settings' => [
                'time_limit' => null,
                'items_per_page' => 10,
                'allow_back_navigation' => true,
                'shuffle_sections' => false,
                'shuffle_items' => false,
            ],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
