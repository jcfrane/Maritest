<?php

namespace App\Models;

use Database\Factories\QuestionnaireFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Questionnaire extends Model
{
    /** @use HasFactory<QuestionnaireFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<QuestionnairePage, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(QuestionnairePage::class)->orderBy('order');
    }

    /**
     * @return HasManyThrough<QuestionnaireItem, QuestionnairePage, $this>
     */
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(QuestionnaireItem::class, QuestionnairePage::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
