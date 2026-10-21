<?php

namespace Database\Factories;

use App\Models\StudySet;
use App\Models\StudySetQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudySetQuestion>
 */
class StudySetQuestionFactory extends Factory
{
    protected $model = StudySetQuestion::class;

    public function definition(): array
    {
        return [
            'study_set_id' => StudySet::factory(),
            'question_type' => 'multiple_choice',
            'difficulty' => 'medium',
            'prompt' => fake()->sentence(8),
            'explanation' => fake()->sentence(12),
            'correct_answer' => 'Option B',
            'sort_order' => 1,
        ];
    }
}
