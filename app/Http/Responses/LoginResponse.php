<?php

namespace App\Http\Responses;

use App\Actions\Tenancy\ResolveTenantRedirect;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private ResolveTenantRedirect $resolveTenantRedirect) {}

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();

        if ($user->isLandlord()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        $rememberedTenantSlug = $request->session()->get(ResolveTenantRedirect::SESSION_KEY);

        $tenant = $this->resolveTenantRedirect->handle(
            $user,
            is_string($rememberedTenantSlug) ? $rememberedTenantSlug : null,
        );

        if ($tenant) {
            return redirect()->intended(route('tenant.dashboard', ['tenant' => $tenant], absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
