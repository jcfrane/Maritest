<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    /**
     * @param  array{name: string, email: string, password: string, role?: string}  $data
     */
    public function handle(Tenant $tenant, array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $tenant->users()->attach($user);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }
}
