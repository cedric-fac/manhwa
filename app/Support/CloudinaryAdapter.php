<?php

namespace App\Support;

use Cloudinary\Cloudinary;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

class CloudinaryAdapter implements FilesystemAdapter
{
    protected Cloudinary $cloudinary;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->cloudinary = new Cloudinary($config['url']);
    }

    public function fileExists(string $path): bool
    {
        try {
            $response = $this->cloudinary->admin()->asset($path);
            return isset($response['public_id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'cloudinary');
            file_put_contents($tempFile, $contents);
            $this->cloudinary->uploadApi()->upload($tempFile, ['public_id' => $path]);
            unlink($tempFile);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'cloudinary');
            $stream = fopen($tempFile, 'w+b');
            stream_copy_to_stream($contents, $stream);
            fclose($stream);
            $this->cloudinary->uploadApi()->upload($tempFile, ['public_id' => $path]);
            unlink($tempFile);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function read(string $path): string
    {
        try {
            $url = $this->cloudinary->image($path)->toUrl();
            return file_get_contents($url);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function readStream(string $path)
    {
        try {
            $url = $this->cloudinary->image($path)->toUrl();
            return fopen($url, 'rb');
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->cloudinary->uploadApi()->destroy($path);
        } catch (\Exception $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage());
        }
    }

    public function deleteDirectory(string $path): void
    {
        try {
            $this->cloudinary->admin()->deleteAssetsByPrefix($path);
        } catch (\Exception $e) {
            throw FilesystemException::from($e);
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Directories are not supported in Cloudinary
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Visibility is not supported in Cloudinary
    }

    public function visibility(string $path): FileAttributes
    {
        // Always public in Cloudinary
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        try {
            $response = $this->cloudinary->admin()->asset($path);
            return new FileAttributes($path, null, null, null, $response['resource_type']);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        try {
            $response = $this->cloudinary->admin()->asset($path);
            return new FileAttributes($path, null, null, $response['created_at']);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $response = $this->cloudinary->admin()->asset($path);
            return new FileAttributes($path, $response['bytes']);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        try {
            $results = $this->cloudinary->admin()->assets(['type' => 'upload', 'prefix' => $path]);
            foreach ($results['resources'] as $resource) {
                yield new FileAttributes(
                    $resource['public_id'],
                    $resource['bytes'],
                    'public',
                    $resource['created_at'],
                    $resource['resource_type']
                );
            }
        } catch (\Exception $e) {
            throw FilesystemException::from($e);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->cloudinary->uploadApi()->rename($source, $destination);
        } catch (\Exception $e) {
            throw FilesystemException::from($e);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $content = $this->read($source);
            $this->write($destination, $content, $config);
        } catch (\Exception $e) {
            throw FilesystemException::from($e);
        }
    }
}