<?php

namespace App\Actions\Users;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUser
{
    use AsAction;

    /**
     * @param  array{name?: string, email?: string, password?: string, role?: string}  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->update(array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => isset($data['password']) ? $data['password'] : null,
        ]));

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh();
    }
}
