<?php

namespace App\Models;

use App\Models\StudySetQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySetQuestionOption extends Model
{
    use HasFactory;

    protected $table = 'study_set_question_options';

    protected $fillable = [
        'study_set_question_id',
        'label',
        'value',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(StudySetQuestion::class, 'study_set_question_id');
    }
}
