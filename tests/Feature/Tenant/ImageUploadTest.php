<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads image to s3 and returns a stable application url', function () {
    Storage::fake('s3');

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    $file = UploadedFile::fake()->image('test-image.jpg', 600, 600);

    $response = $this->actingAs($user)->postJson(route('tenant.images.store', ['tenant' => $tenant->slug]), [
        'image' => $file,
    ]);

    $response->assertOk()->assertExactJson([
        'url' => route('tenant.images.show', [
            'tenant' => $tenant->slug,
            'image' => $file->hashName(),
        ], false),
    ]);

    Storage::disk('s3')->assertExists('tenant/'.$tenant->slug.'/images/'.$file->hashName());
});

it('validates image upload size is max 5mb', function () {
    Storage::fake('s3');

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $tenant->users()->attach($user);

    // Create a 6MB file
    $file = UploadedFile::fake()->create('test.jpg', 6144, 'image/jpeg');

    $response = $this->actingAs($user)->postJson(route('tenant.images.store', ['tenant' => $tenant->slug]), [
        'image' => $file,
    ]);

    $response->assertJsonValidationErrors(['image']);
});

it('serves uploaded images without authentication', function () {
    Storage::fake('s3');

    $tenant = Tenant::factory()->create();
    $file = UploadedFile::fake()->image('test-image.png', 300, 300);
    $filename = $file->hashName();

    Storage::disk('s3')->putFileAs('tenant/'.$tenant->slug.'/images', $file, $filename);

    $response = $this->get(route('tenant.images.show', [
        'tenant' => $tenant->slug,
        'image' => $filename,
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'image/png');
});

it('returns not found when an image is missing', function () {
    Storage::fake('s3');

    $tenant = Tenant::factory()->create();

    $this->get(route('tenant.images.show', [
        'tenant' => $tenant->slug,
        'image' => 'missing-image.png',
    ]))->assertNotFound();
});
