<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Create landlord user
        $landlord = User::factory()->landlord()->create([
            'name' => 'Landlord Admin',
            'email' => 'admin@maritest.com',
        ]);
        $landlord->assignRole('landlord');

        // Create a sample tenant
        $tenant = Tenant::factory()->create([
            'name' => 'Proact',
            'slug' => 'proact',
        ]);

        // Create tenant admin
        $tenantAdmin = User::factory()->create([
            'name' => 'JC Frane',
            'email' => 'admin@proact.com',
        ]);
        $tenantAdmin->assignRole('tenant-admin');
        $tenant->users()->attach($tenantAdmin);

        // Create a candidate
        $candidate = User::factory()->create([
            'name' => 'John Student',
            'email' => 'student@example.com',
        ]);
        $candidate->assignRole('candidate');
        $tenant->users()->attach($candidate);
    }
}
