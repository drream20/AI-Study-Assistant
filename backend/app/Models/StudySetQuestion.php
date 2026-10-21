<?php

namespace App\Models;

use App\Models\StudySetQuestionOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySetQuestion extends Model
{
    use HasFactory;

    protected $table = 'study_set_questions';

    protected $fillable = [
        'study_set_id',
        'question_type',
        'difficulty',
        'prompt',
        'explanation',
        'correct_answer',
        'sort_order',
    ];

    public function studySet(): BelongsTo
    {
        return $this->belongsTo(StudySet::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(StudySetQuestionOption::class);
    }
}
