<?php

namespace Database\Factories;

use App\Models\StudySet;
use App\Models\StudySetFlashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudySetFlashcard>
 */
class StudySetFlashcardFactory extends Factory
{
    protected $model = StudySetFlashcard::class;

    public function definition(): array
    {
        return [
            'study_set_id' => StudySet::factory(),
            'front' => fake()->sentence(4),
            'back' => fake()->sentence(8),
            'review_status' => 'new',
            'due_at' => now()->addDay(),
            'interval_days' => 1,
            'review_count' => 0,
            'ease_factor' => 2.5,
        ];
    }
}
