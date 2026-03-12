<?php

namespace App\Http\Middleware;

use App\Actions\Tenancy\ResolveTenantRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectTenantUsersToTenantRoutes
{
    public function __construct(private ResolveTenantRedirect $resolveTenantRedirect) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isLandlord()) {
            return $next($request);
        }

        $rememberedTenantSlug = $request->session()->get(ResolveTenantRedirect::SESSION_KEY);

        $tenant = $this->resolveTenantRedirect->handle(
            $user,
            is_string($rememberedTenantSlug) ? $rememberedTenantSlug : null,
        );

        if (! $tenant) {
            return $next($request);
        }

        return redirect()->route('tenant.dashboard', $tenant);
    }
}
