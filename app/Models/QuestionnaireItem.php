<?php

namespace App\Models;

use Database\Factories\QuestionnaireItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireItem extends Model
{
    /** @use HasFactory<QuestionnaireItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'questionnaire_page_id',
        'type',
        'content',
        'required',
        'order',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'order' => 'integer',
            'properties' => 'array',
        ];
    }

    /**
     * @return BelongsTo<QuestionnairePage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(QuestionnairePage::class, 'questionnaire_page_id');
    }

    /**
     * @return HasMany<QuestionnaireChoice, $this>
     */
    public function choices(): HasMany
    {
        return $this->hasMany(QuestionnaireChoice::class)->orderBy('order');
    }

    public function isInstruction(): bool
    {
        return $this->type === 'instruction';
    }

    public function isQuestion(): bool
    {
        return ! $this->isInstruction();
    }
}
