<?php

namespace App\Actions\Questionnaires;

use App\Models\Questionnaire;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteQuestionnaire
{
    use AsAction;

    public function handle(Questionnaire $questionnaire): void
    {
        $questionnaire->delete();
    }
}
