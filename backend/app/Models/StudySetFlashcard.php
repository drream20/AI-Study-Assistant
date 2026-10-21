<?php

namespace App\Models;

use App\Models\StudySet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySetFlashcard extends Model
{
    use HasFactory;

    protected $table = 'study_set_flashcards';

    protected $fillable = [
        'study_set_id',
        'front',
        'back',
        'review_status',
        'due_at',
        'interval_days',
        'review_count',
        'ease_factor',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function studySet(): BelongsTo
    {
        return $this->belongsTo(StudySet::class);
    }
}
