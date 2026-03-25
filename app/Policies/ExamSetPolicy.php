<?php

namespace App\Policies;

use App\Models\ExamSet;
use App\Models\User;

class ExamSetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $this->hasAnyExamSetPermission($user);
    }

    public function view(User $user, ExamSet $examSet): bool
    {
        return $user->isLandlord() || $this->hasAnyExamSetPermission($user);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('create-exam-set');
    }

    public function update(User $user, ExamSet $examSet): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-exam-sets')
            || $user->hasPermissionTo('edit-exam-set');
    }

    public function delete(User $user, ExamSet $examSet): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-exam-sets')
            || $user->hasPermissionTo('delete-exam-set');
    }

    public function publish(User $user, ?ExamSet $examSet = null): bool
    {
        return $user->isLandlord()
            || $user->hasPermissionTo('manage-exam-sets')
            || $user->hasPermissionTo('publish-exam-set');
    }

    private function hasAnyExamSetPermission(User $user): bool
    {
        return $user->hasAnyPermission([
            'manage-exam-sets',
            'create-exam-set',
            'edit-exam-set',
            'delete-exam-set',
            'publish-exam-set',
        ]);
    }
}
