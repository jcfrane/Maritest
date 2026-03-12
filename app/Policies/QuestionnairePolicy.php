<?php

namespace App\Policies;

use App\Models\Questionnaire;
use App\Models\User;

class QuestionnairePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('manage-questionnaires');
    }

    public function view(User $user, Questionnaire $questionnaire): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-questionnaires')
            || $questionnaire->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('create-questionnaire');
    }

    public function update(User $user, Questionnaire $questionnaire): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-questionnaires')
            || ($user->hasPermissionTo('create-questionnaire') && $questionnaire->user_id === $user->id);
    }

    public function delete(User $user, Questionnaire $questionnaire): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-questionnaires')
            || ($user->hasPermissionTo('create-questionnaire') && $questionnaire->user_id === $user->id);
    }
}
