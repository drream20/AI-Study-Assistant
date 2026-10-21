<?php

namespace App\Http\Controllers;

use App\Models\StudySet;
use App\Models\StudySetQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class StudySetQuestionController extends Controller
{
    public function store(Request $request, StudySet $studySet): JsonResponse
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return $this->validationResponse($validator);
        }

        $validated = $validator->validated();
        $question = DB::transaction(function () use ($studySet, $validated) {
            $question = $studySet->questions()->create([
                'question_type' => 'multiple_choice',
                'difficulty' => 'medium',
                'prompt' => $validated['prompt'],
                'explanation' => $validated['explanation'] ?? null,
                'correct_answer' => $validated['options'][$validated['correct']],
                'sort_order' => ($studySet->questions()->max('sort_order') ?? 0) + 1,
            ]);

            $question->options()->createMany($this->optionsFor($validated));

            return $question->load('options');
        });

        return response()->json($question, 201);
    }

    public function update(Request $request, StudySet $studySet, StudySetQuestion $question): JsonResponse
    {
        if ($question->study_set_id !== $studySet->id) {
            abort(404);
        }

        $validator = $this->validator($request);

        if ($validator->fails()) {
            return $this->validationResponse($validator);
        }

        $validated = $validator->validated();
        DB::transaction(function () use ($question, $validated) {
            $question->update([
                'prompt' => $validated['prompt'],
                'explanation' => $validated['explanation'] ?? null,
                'correct_answer' => $validated['options'][$validated['correct']],
            ]);
            $question->options()->delete();
            $question->options()->createMany($this->optionsFor($validated));
        });

        return response()->json($question->fresh('options'), 200);
    }

    public function destroy(StudySet $studySet, StudySetQuestion $question): JsonResponse
    {
        if ($question->study_set_id !== $studySet->id) {
            abort(404);
        }

        $question->delete();

        return response()->json(null, 204);
    }

    private function validator(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'prompt' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2', 'max:26'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct' => ['required', 'integer', 'min:0'],
            'explanation' => ['nullable', 'string'],
        ])->after(function (ValidationValidator $validator) use ($request) {
            $options = $request->input('options');
            $correct = $request->input('correct');

            if (is_array($options) && is_numeric($correct) && (int) $correct >= count($options)) {
                $validator->errors()->add('correct', 'The selected correct answer must match an option.');
            }
        });
    }

    private function validationResponse(ValidationValidator $validator): JsonResponse
    {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    private function optionsFor(array $validated): array
    {
        return array_map(function (string $value, int $index) use ($validated) {
            return [
                'label' => chr(65 + $index),
                'value' => $value,
                'is_correct' => $index === $validated['correct'],
            ];
        }, $validated['options'], array_keys($validated['options']));
    }
}