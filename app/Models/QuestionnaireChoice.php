<?php

namespace App\Models;

use Database\Factories\QuestionnaireChoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireChoice extends Model
{
    /** @use HasFactory<QuestionnaireChoiceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'questionnaire_item_id',
        'content',
        'is_correct',
        'order',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order' => 'integer',
            'properties' => 'array',
        ];
    }

    /**
     * @return BelongsTo<QuestionnaireItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireItem::class, 'questionnaire_item_id');
    }
}
