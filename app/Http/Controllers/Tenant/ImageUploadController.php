<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Tenant\ShowTenantImage;
use App\Actions\Tenant\StoreTenantImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImageUploadRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageUploadController extends Controller
{
    public function store(Tenant $tenant, ImageUploadRequest $request): JsonResponse
    {
        return response()->json([
            'url' => StoreTenantImage::run($tenant, $request->file('image')),
        ]);
    }

    public function show(Tenant $tenant, string $image): StreamedResponse
    {
        return ShowTenantImage::run($tenant, $image);
    }
}
