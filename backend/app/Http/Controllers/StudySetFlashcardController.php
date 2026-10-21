<?php

namespace App\Http\Controllers;

use App\Models\StudySetFlashcard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudySetFlashcardController extends Controller
{
    public function review(Request $request, StudySetFlashcard $flashcard): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:known,review'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $validator->validated()['status'];
        $intervalDays = $status === 'known'
            ? max(1, $flashcard->interval_days * 2)
            : 0;

        $flashcard->update([
            'review_status' => $status,
            'review_count' => $flashcard->review_count + 1,
            'interval_days' => $intervalDays,
            'due_at' => now()->addDays($intervalDays),
        ]);
        $flashcard->studySet()->update(['last_reviewed_at' => now()]);

        return response()->json($flashcard->fresh(), 200);
    }
}