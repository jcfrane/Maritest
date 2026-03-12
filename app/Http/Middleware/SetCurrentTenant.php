<?php

namespace App\Http\Middleware;

use App\Actions\Tenancy\ResolveTenantRedirect;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SetCurrentTenant
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (! $tenant instanceof Tenant) {
            $tenant = Tenant::where('slug', $tenant)->first();

            if (! $tenant) {
                throw new NotFoundHttpException('Tenant not found.');
            }

            $request->route()->setParameter('tenant', $tenant);
        }

        if (! $tenant->is_active) {
            throw new NotFoundHttpException('Tenant not found.');
        }

        $user = $request->user();

        if ($user && ! $user->isLandlord() && ! $user->belongsToTenant($tenant)) {
            throw new AccessDeniedHttpException('You do not have access to this tenant.');
        }

        $tenant->makeCurrent();

        $response = $next($request);

        if ($response->isSuccessful() || $response->isRedirection()) {
            $request->session()->put(ResolveTenantRedirect::SESSION_KEY, $tenant->slug);
        }

        return $response;
    }
}
