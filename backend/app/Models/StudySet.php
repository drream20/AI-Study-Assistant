<?php

namespace App\Models;

use App\Models\StudySetFlashcard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'subject',
        'description',
        'status',
        'source_type',
        'source_file_name',
        'generation_meta',
        'is_favorite',
        'last_reviewed_at',
    ];

    protected $casts = [
        'generation_meta' => 'array',
        'is_favorite' => 'boolean',
        'last_reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(StudySetQuestion::class)->orderBy('sort_order');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(StudySetFlashcard::class)->orderBy('created_at');
    }
}
