<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;

    public function handle(Tenant $tenant, User $user): void
    {
        $tenant->users()->detach($user);

        if ($user->tenants()->count() === 0) {
            $user->delete();
        }
    }
}
