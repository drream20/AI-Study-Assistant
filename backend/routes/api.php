<?php

use App\Http\Controllers\StudySetController;
use App\Http\Controllers\StudySetFlashcardController;
use App\Http\Controllers\StudySetQuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'AI Study Assistant',
    ]);
});

Route::get('/study-sets', [StudySetController::class, 'index']);
Route::post('/study-sets', [StudySetController::class, 'store']);
Route::patch('/study-sets/{studySet}', [StudySetController::class, 'update']);
Route::delete('/study-sets/{studySet}', [StudySetController::class, 'destroy']);
Route::post('/study-sets/{studySet}/questions', [StudySetQuestionController::class, 'store']);
Route::patch('/study-sets/{studySet}/questions/{question}', [StudySetQuestionController::class, 'update']);
Route::delete('/study-sets/{studySet}/questions/{question}', [StudySetQuestionController::class, 'destroy']);
Route::patch('/study-set-flashcards/{flashcard}/review', [StudySetFlashcardController::class, 'review']);
