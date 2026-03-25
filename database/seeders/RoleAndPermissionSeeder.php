<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Tenant management
            'manage-tenants',

            // Questionnaires
            'manage-questionnaires',
            'create-questionnaire',
            'edit-questionnaire',
            'delete-questionnaire',
            'publish-questionnaire',

            // Exam sets
            'manage-exam-sets',
            'create-exam-set',
            'edit-exam-set',
            'delete-exam-set',
            'publish-exam-set',

            // Schedules
            'manage-schedules',
            'create-schedule',
            'edit-schedule',
            'delete-schedule',

            // Users
            'manage-users',
            'create-user',
            'invite-user',
            'delete-user',

            // Submissions
            'view-submissions',
            'grade-submissions',
            'export-submissions',

            // Exams
            'take-exams',
            'view-own-results',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $landlord = Role::firstOrCreate(['name' => 'landlord']);
        $landlord->syncPermissions(Permission::all());

        $tenantAdmin = Role::firstOrCreate(['name' => 'tenant-admin']);
        $tenantAdmin->syncPermissions([
            'manage-questionnaires', 'create-questionnaire', 'edit-questionnaire',
            'delete-questionnaire', 'publish-questionnaire',
            'manage-exam-sets', 'create-exam-set', 'edit-exam-set',
            'delete-exam-set', 'publish-exam-set',
            'manage-schedules', 'create-schedule', 'edit-schedule', 'delete-schedule',
            'manage-users', 'create-user', 'invite-user', 'delete-user',
            'view-submissions', 'grade-submissions', 'export-submissions',
        ]);

        $tenantManager = Role::firstOrCreate(['name' => 'tenant-manager']);
        $tenantManager->syncPermissions([
            'manage-questionnaires', 'create-questionnaire', 'edit-questionnaire',
            'publish-questionnaire',
            'manage-exam-sets', 'create-exam-set', 'edit-exam-set',
            'publish-exam-set',
            'manage-schedules', 'create-schedule', 'edit-schedule',
            'view-submissions', 'grade-submissions',
        ]);

        $tenantMember = Role::firstOrCreate(['name' => 'tenant-member']);
        $tenantMember->syncPermissions([
            'view-submissions',
        ]);

        $candidate = Role::firstOrCreate(['name' => 'candidate']);
        $candidate->syncPermissions([
            'take-exams',
            'view-own-results',
        ]);
    }
}
