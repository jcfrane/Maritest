<?php

namespace App\TenantFinders;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class PathTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $slug = $request->route('tenant');

        if (! $slug) {
            return null;
        }

        if ($slug instanceof Tenant) {
            return $slug;
        }

        return Tenant::where('slug', $slug)->first();
    }
}
