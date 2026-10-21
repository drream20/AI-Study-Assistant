<?php

namespace Database\Factories;

use App\Models\StudySet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudySet>
 */
class StudySetFactory extends Factory
{
    protected $model = StudySet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'subject' => fake()->randomElement(['Biology', 'Mathematics', 'History', 'Chemistry', 'Literature']),
            'description' => fake()->paragraph(),
            'status' => 'ready',
            'source_type' => 'upload',
            'source_file_name' => fake()->word() . '.pdf',
            'generation_meta' => [
                'questions' => 10,
                'difficulty' => 'medium',
                'language' => 'en',
            ],
            'is_favorite' => fake()->boolean(),
            'last_reviewed_at' => now()->subDays(fake()->numberBetween(1, 15)),
        ];
    }
}
