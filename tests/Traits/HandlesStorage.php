<?php

namespace Tests\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesStorage
{
    /**
     * Setup the storage disk for testing.
     */
    protected function setupTestStorage(): void
    {
        Storage::fake('test');
        Storage::fake('cloudinary');
    }

    /**
     * Create a fake meter reading image.
     */
    protected function createFakeMeterImage(string $name = 'meter.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name)->size(100);
    }

    /**
     * Assert that a file exists in the test storage.
     */
    protected function assertStorageHasFile(string $path): void
    {
        Storage::disk('test')->assertExists($path);
    }

    /**
     * Assert that a file does not exist in the test storage.
     */
    protected function assertStorageDoesNotHaveFile(string $path): void
    {
        Storage::disk('test')->assertMissing($path);
    }

    /**
     * Get the URL for a stored file.
     */
    protected function getStorageFileUrl(string $path): string
    {
        return Storage::disk('test')->url($path);
    }

    /**
     * Clean up test storage.
     */
    protected function cleanupTestStorage(): void
    {
        if (file_exists(storage_path('app/test'))) {
            Storage::disk('test')->deleteDirectory('/');
        }
    }
}