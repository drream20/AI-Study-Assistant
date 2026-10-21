<?php

namespace Database\Seeders;

use App\Models\StudySet;
use App\Models\StudySetFlashcard;
use App\Models\StudySetQuestion;
use App\Models\StudySetQuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo Student',
            'email' => 'student@example.com',
        ]);

        $studySets = [
            [
                'title' => 'Biology Fundamentals',
                'subject' => 'Biology',
                'description' => 'Core cell biology concepts for revision.',
                'status' => 'ready',
                'source_type' => 'upload',
                'generation_meta' => [
                    'questions' => 5,
                    'difficulty' => 'medium',
                    'language' => 'en',
                ],
                'questions' => [
                    [
                        'prompt' => 'What is the powerhouse of the cell?',
                        'question_type' => 'multiple_choice',
                        'difficulty' => 'medium',
                        'explanation' => 'Mitochondria generate ATP, which powers the cell.',
                        'correct_answer' => 'Mitochondria',
                        'options' => [
                            ['label' => 'A', 'value' => 'Nucleus', 'is_correct' => false],
                            ['label' => 'B', 'value' => 'Mitochondria', 'is_correct' => true],
                            ['label' => 'C', 'value' => 'Cell wall', 'is_correct' => false],
                            ['label' => 'D', 'value' => 'Ribosome', 'is_correct' => false],
                        ],
                    ],
                    [
                        'prompt' => 'Which organelle is responsible for photosynthesis?',
                        'question_type' => 'multiple_choice',
                        'difficulty' => 'easy',
                        'explanation' => 'Chloroplasts capture sunlight and convert it to chemical energy.',
                        'correct_answer' => 'Chloroplast',
                        'options' => [
                            ['label' => 'A', 'value' => 'Chloroplast', 'is_correct' => true],
                            ['label' => 'B', 'value' => 'Golgi apparatus', 'is_correct' => false],
                            ['label' => 'C', 'value' => 'Lysosome', 'is_correct' => false],
                            ['label' => 'D', 'value' => 'Cytoplasm', 'is_correct' => false],
                        ],
                    ],
                ],
                'flashcards' => [
                    ['front' => 'What is ATP?', 'back' => 'Adenosine triphosphate, the cell’s main energy currency.', 'review_status' => 'new'],
                    ['front' => 'What do mitochondria do?', 'back' => 'They produce ATP through cellular respiration.', 'review_status' => 'learning'],
                ],
            ],
            [
                'title' => 'World History Timeline',
                'subject' => 'History',
                'description' => 'Key events and civilizations for quick review.',
                'status' => 'ready',
                'source_type' => 'upload',
                'generation_meta' => [
                    'questions' => 4,
                    'difficulty' => 'easy',
                    'language' => 'en',
                ],
                'questions' => [
                    [
                        'prompt' => 'Which ancient civilization built pyramids?',
                        'question_type' => 'multiple_choice',
                        'difficulty' => 'easy',
                        'explanation' => 'The ancient Egyptians built massive pyramid tombs.',
                        'correct_answer' => 'Ancient Egyptians',
                        'options' => [
                            ['label' => 'A', 'value' => 'Romans', 'is_correct' => false],
                            ['label' => 'B', 'value' => 'Ancient Egyptians', 'is_correct' => true],
                            ['label' => 'C', 'value' => 'Mongols', 'is_correct' => false],
                            ['label' => 'D', 'value' => 'Greeks', 'is_correct' => false],
                        ],
                    ],
                ],
                'flashcards' => [
                    ['front' => 'When was the Renaissance?', 'back' => 'It began in the 14th century and spread across Europe.', 'review_status' => 'new'],
                ],
            ],
        ];

        foreach ($studySets as $studySetData) {
            $studySet = StudySet::factory()->for($user)->create([
                'title' => $studySetData['title'],
                'subject' => $studySetData['subject'],
                'description' => $studySetData['description'],
                'status' => $studySetData['status'],
                'source_type' => $studySetData['source_type'],
                'generation_meta' => $studySetData['generation_meta'],
            ]);

            foreach ($studySetData['questions'] as $index => $questionData) {
                $question = StudySetQuestion::create([
                    'study_set_id' => $studySet->id,
                    'question_type' => $questionData['question_type'],
                    'difficulty' => $questionData['difficulty'],
                    'prompt' => $questionData['prompt'],
                    'explanation' => $questionData['explanation'],
                    'correct_answer' => $questionData['correct_answer'],
                    'sort_order' => $index + 1,
                ]);

                foreach ($questionData['options'] as $optionData) {
                    StudySetQuestionOption::create([
                        'study_set_question_id' => $question->id,
                        'label' => $optionData['label'],
                        'value' => $optionData['value'],
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }
            }

            foreach ($studySetData['flashcards'] as $flashcardData) {
                StudySetFlashcard::create([
                    'study_set_id' => $studySet->id,
                    'front' => $flashcardData['front'],
                    'back' => $flashcardData['back'],
                    'review_status' => $flashcardData['review_status'],
                    'due_at' => now()->addDays(1),
                    'interval_days' => 1,
                    'review_count' => 0,
                    'ease_factor' => 2.5,
                ]);
            }
        }
    }
}

