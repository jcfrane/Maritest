<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTenantImage
{
    use AsAction;

    public function handle(Tenant $tenant, UploadedFile $image): string
    {
        $filename = $image->hashName();
        Storage::disk('s3')->putFileAs(
            $this->directory($tenant),
            $image,
            $filename,
        );

        return route('tenant.images.show', [
            'tenant' => $tenant,
            'image' => $filename,
        ], false);
    }

    private function directory(Tenant $tenant): string
    {
        return 'tenant/'.$tenant->slug.'/images';
    }
}
