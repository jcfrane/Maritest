<?php

namespace App\Models;

use Database\Factories\QuestionnairePageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnairePage extends Model
{
    /** @use HasFactory<QuestionnairePageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'questionnaire_id',
        'title',
        'description',
        'order',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Questionnaire, $this>
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * @return HasMany<QuestionnaireItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuestionnaireItem::class)->orderBy('order');
    }
}
