<?php

namespace App\Http\Controllers;

use App\Models\StudySet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudySetController extends Controller
{
    public function index(): JsonResponse
    {
        $studySets = StudySet::with(['questions.options', 'flashcards'])
            ->latest()
            ->get();

        return response()->json($studySets->map(function (StudySet $studySet) {
            return [
                'id' => $studySet->id,
                'user_id' => $studySet->user_id,
                'title' => $studySet->title,
                'subject' => $studySet->subject,
                'description' => $studySet->description,
                'status' => $studySet->status,
                'source_type' => $studySet->source_type,
                'source_file_name' => $studySet->source_file_name,
                'is_favorite' => (bool) $studySet->is_favorite,
                'last_reviewed_at' => $studySet->last_reviewed_at?->toISOString(),
                'questions' => $studySet->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'study_set_id' => $question->study_set_id,
                        'question_type' => $question->question_type,
                        'difficulty' => $question->difficulty,
                        'prompt' => $question->prompt,
                        'explanation' => $question->explanation,
                        'correct_answer' => $question->correct_answer,
                        'sort_order' => $question->sort_order,
                        'options' => $question->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'label' => $option->label,
                                'value' => $option->value,
                                'is_correct' => (bool) $option->is_correct,
                            ];
                        })->values(),
                    ];
                })->values(),
                'flashcards' => $studySet->flashcards->map(function ($flashcard) {
                    return [
                        'id' => $flashcard->id,
                        'study_set_id' => $flashcard->study_set_id,
                        'front' => $flashcard->front,
                        'back' => $flashcard->back,
                        'review_status' => $flashcard->review_status,
                        'due_at' => $flashcard->due_at?->toISOString(),
                        'interval_days' => $flashcard->interval_days,
                        'review_count' => $flashcard->review_count,
                        'ease_factor' => $flashcard->ease_factor,
                    ];
                })->values(),
                'created_at' => $studySet->created_at?->toISOString(),
                'updated_at' => $studySet->updated_at?->toISOString(),
            ];
        }));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,ready,archived'],
            'source_type' => ['nullable', 'string', 'in:upload,ocr,manual'],
            'source_file_name' => ['nullable', 'string', 'max:255'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $studySet = StudySet::create($request->only([
            'user_id',
            'title',
            'subject',
            'description',
            'status',
            'source_type',
            'source_file_name',
            'is_favorite',
        ]));

        return response()->json($studySet->fresh(), 201);
    }

    public function update(Request $request, StudySet $studySet): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subject' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $studySet->update($validator->validated());

        return response()->json($studySet->fresh(), 200);
    }

    public function destroy(StudySet $studySet): JsonResponse
    {
        $studySet->delete();

        return response()->json(null, 204);
    }
}
