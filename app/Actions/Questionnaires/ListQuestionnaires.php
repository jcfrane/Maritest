<?php

namespace App\Actions\Questionnaires;

use App\Models\Questionnaire;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class ListQuestionnaires
{
    use AsAction;

    /**
     * @return Collection<int, Questionnaire>
     */
    public function handle(Tenant $tenant, User $user): Collection
    {
        $query = $tenant->questionnaires()->with('pages.items.choices');

        if (! $user->isLandlord() && ! $user->hasPermissionTo('manage-questionnaires')) {
            $query->where('user_id', $user->id);
        }

        return $query->latest()->get();
    }
}
