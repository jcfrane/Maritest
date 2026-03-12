<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveTenantRedirect
{
    use AsAction;

    public const SESSION_KEY = 'last_visited_tenant_slug';

    public function handle(User $user, ?string $tenantSlug = null): ?Tenant
    {
        if ($tenantSlug) {
            $rememberedTenant = $user->tenants()
                ->where('tenants.slug', $tenantSlug)
                ->where('tenants.is_active', true)
                ->first();

            if ($rememberedTenant) {
                return $rememberedTenant;
            }
        }

        return $user->tenants()
            ->where('tenants.is_active', true)
            ->orderByPivot('created_at')
            ->first();
    }
}
