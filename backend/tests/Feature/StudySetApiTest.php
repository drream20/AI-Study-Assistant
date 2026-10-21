<?php

namespace Tests\Feature;

use App\Models\StudySet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudySetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_seeded_study_sets_for_the_ui(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo Student',
            'email' => 'student@example.com',
        ]);

        $studySet = StudySet::factory()->for($user)->create([
            'title' => 'Biology Fundamentals',
            'subject' => 'Biology',
            'status' => 'ready',
        ]);

        $studySet->questions()->create([
            'prompt' => 'What is the powerhouse of the cell?',
            'question_type' => 'multiple_choice',
            'difficulty' => 'medium',
            'explanation' => 'Mitochondria generate ATP for the cell.',
        ]);

        $studySet->flashcards()->create([
            'front' => 'Mitochondria',
            'back' => 'Organelles that produce energy for the cell.',
            'review_status' => 'new',
        ]);

        $response = $this->getJson('/api/study-sets');

        $response->assertOk()
            ->assertJsonPath('0.title', 'Biology Fundamentals')
            ->assertJsonPath('0.subject', 'Biology')
            ->assertJsonCount(1, '0.questions')
            ->assertJsonCount(1, '0.flashcards');
    }

    public function test_it_can_create_a_study_set_via_the_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/study-sets', [
            'user_id' => $user->id,
            'title' => 'Cell Structure',
            'subject' => 'Biology',
            'description' => 'Organelles and their functions.',
            'status' => 'draft',
            'source_type' => 'upload',
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Cell Structure')
            ->assertJsonPath('subject', 'Biology');

        $this->assertDatabaseHas('study_sets', [
            'title' => 'Cell Structure',
            'subject' => 'Biology',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_can_update_a_study_set_via_the_api(): void
    {
        $studySet = StudySet::factory()->create([
            'title' => 'Biology Draft',
            'subject' => 'General',
        ]);

        $response = $this->patchJson("/api/study-sets/{$studySet->id}", [
            'title' => 'Cell Biology',
            'subject' => 'Biology',
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'Cell Biology')
            ->assertJsonPath('subject', 'Biology');

        $this->assertDatabaseHas('study_sets', [
            'id' => $studySet->id,
            'title' => 'Cell Biology',
            'subject' => 'Biology',
        ]);
    }

    public function test_it_rejects_an_empty_study_set_title(): void
    {
        $studySet = StudySet::factory()->create();

        $this->patchJson("/api/study-sets/{$studySet->id}", [
            'title' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_it_deletes_a_study_set_and_its_children(): void
    {
        $studySet = StudySet::factory()->create();
        $question = $studySet->questions()->create([
            'prompt' => 'What is ATP?',
            'question_type' => 'multiple_choice',
            'difficulty' => 'easy',
        ]);
        $question->options()->create([
            'label' => 'A',
            'value' => 'Cell energy',
            'is_correct' => true,
        ]);
        $flashcard = $studySet->flashcards()->create([
            'front' => 'ATP',
            'back' => 'The cell\'s main energy currency.',
        ]);

        $this->deleteJson("/api/study-sets/{$studySet->id}")->assertNoContent();

        $this->assertDatabaseMissing('study_sets', ['id' => $studySet->id]);
        $this->assertDatabaseMissing('study_set_questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('study_set_question_options', [
            'study_set_question_id' => $question->id,
        ]);
        $this->assertDatabaseMissing('study_set_flashcards', ['id' => $flashcard->id]);
    }

    public function test_it_can_create_a_question_with_answer_options(): void
    {
        $studySet = StudySet::factory()->create();

        $response = $this->postJson("/api/study-sets/{$studySet->id}/questions", [
            'prompt' => 'Which organelle produces ATP?',
            'options' => ['Nucleus', 'Mitochondria'],
            'correct' => 1,
            'explanation' => 'Mitochondria produce most of the cell\'s ATP.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('prompt', 'Which organelle produces ATP?')
            ->assertJsonPath('correct_answer', 'Mitochondria')
            ->assertJsonPath('options.1.is_correct', true);

        $this->assertDatabaseHas('study_set_question_options', [
            'study_set_question_id' => $response->json('id'),
            'label' => 'B',
            'value' => 'Mitochondria',
            'is_correct' => true,
        ]);
    }

    public function test_it_can_update_a_question_and_its_options(): void
    {
        $studySet = StudySet::factory()->create();
        $question = $studySet->questions()->create([
            'prompt' => 'Old prompt',
            'question_type' => 'multiple_choice',
            'difficulty' => 'medium',
            'correct_answer' => 'Old answer',
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'value' => 'Old answer', 'is_correct' => true],
            ['label' => 'B', 'value' => 'Other answer', 'is_correct' => false],
        ]);

        $this->patchJson("/api/study-sets/{$studySet->id}/questions/{$question->id}", [
            'prompt' => 'Updated prompt',
            'options' => ['Choice one', 'Choice two'],
            'correct' => 0,
        ])->assertOk()
            ->assertJsonPath('prompt', 'Updated prompt')
            ->assertJsonPath('options.0.value', 'Choice one')
            ->assertJsonPath('options.0.is_correct', true);

        $this->assertDatabaseHas('study_set_questions', [
            'id' => $question->id,
            'correct_answer' => 'Choice one',
        ]);
        $this->assertDatabaseMissing('study_set_question_options', [
            'study_set_question_id' => $question->id,
            'value' => 'Old answer',
        ]);
    }

    public function test_it_rejects_a_correct_answer_index_without_an_option(): void
    {
        $studySet = StudySet::factory()->create();

        $this->postJson("/api/study-sets/{$studySet->id}/questions", [
            'prompt' => 'Pick one',
            'options' => ['A', 'B'],
            'correct' => 2,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('correct');
    }

    public function test_it_can_delete_a_question_and_its_options(): void
    {
        $studySet = StudySet::factory()->create();
        $question = $studySet->questions()->create([
            'prompt' => 'Temporary question',
            'question_type' => 'multiple_choice',
            'difficulty' => 'easy',
        ]);
        $question->options()->create([
            'label' => 'A',
            'value' => 'Temporary option',
            'is_correct' => true,
        ]);

        $this->deleteJson("/api/study-sets/{$studySet->id}/questions/{$question->id}")->assertNoContent();

        $this->assertDatabaseMissing('study_set_questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('study_set_question_options', [
            'study_set_question_id' => $question->id,
        ]);
    }

    public function test_it_persists_flashcard_review_progress(): void
    {
        $studySet = StudySet::factory()->create();
        $flashcard = $studySet->flashcards()->create([
            'front' => 'ATP',
            'back' => 'Cell energy currency',
            'review_status' => 'new',
        ]);

        $this->patchJson("/api/study-set-flashcards/{$flashcard->id}/review", [
            'status' => 'known',
        ])->assertOk()
            ->assertJsonPath('review_status', 'known')
            ->assertJsonPath('review_count', 1)
            ->assertJsonPath('interval_days', 1);

        $this->assertDatabaseHas('study_set_flashcards', [
            'id' => $flashcard->id,
            'review_status' => 'known',
            'review_count' => 1,
            'interval_days' => 1,
        ]);
        $this->assertNotNull($studySet->fresh()->last_reviewed_at);
    }
}
