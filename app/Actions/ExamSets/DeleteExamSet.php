<?php

namespace App\Actions\ExamSets;

use App\Models\ExamSet;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteExamSet
{
    use AsAction;

    public function handle(ExamSet $examSet): void
    {
        $examSet->delete();
    }
}
