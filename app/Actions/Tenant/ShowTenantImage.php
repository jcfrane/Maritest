<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShowTenantImage
{
    use AsAction;

    public function handle(Tenant $tenant, string $image): StreamedResponse
    {
        abort_unless($tenant->is_active, 404);

        try {
            $stream = Storage::disk('s3')->readStream($this->path($tenant, $image));
        } catch (UnableToReadFile) {
            abort(404);
        }

        abort_unless(is_resource($stream), 404);

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $this->contentType($image),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function path(Tenant $tenant, string $image): string
    {
        return 'tenant/'.$tenant->slug.'/images/'.$image;
    }

    private function contentType(string $image): string
    {
        return match (strtolower(pathinfo($image, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'avif' => 'image/avif',
            default => 'application/octet-stream',
        };
    }
}
