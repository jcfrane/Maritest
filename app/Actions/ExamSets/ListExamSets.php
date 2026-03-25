<?php

namespace App\Actions\ExamSets;

use App\Models\ExamSet;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class ListExamSets
{
    use AsAction;

    /**
     * @return Collection<int, ExamSet>
     */
    public function handle(Tenant $tenant): Collection
    {
        return $tenant->examSets()
            ->with('questionnaires:id,title,description,status')
            ->latest()
            ->get();
    }
}
