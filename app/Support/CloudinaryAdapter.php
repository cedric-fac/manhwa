<?php

namespace App\Support;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToWriteFile;
use Cloudinary\Cloudinary;

class CloudinaryAdapter implements FilesystemAdapter
{
    protected $client;
    
    public function __construct(Cloudinary $client)
    {
        $this->client = $client;
    }

    public function fileExists(string $path): bool
    {
        try {
            $this->client->uploadApi()->asset($path);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->client->uploadApi()->upload(
                $contents,
                ['public_id' => $this->getPublicId($path)]
            );
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            $this->client->uploadApi()->upload(
                $contents,
                ['public_id' => $this->getPublicId($path)]
            );
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function read(string $path): string
    {
        try {
            $url = $this->client->uploadApi()->asset($path)['secure_url'];
            return file_get_contents($url);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function readStream(string $path)
    {
        try {
            $url = $this->client->uploadApi()->asset($path)['secure_url'];
            return fopen($url, 'rb');
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->client->uploadApi()->destroy($this->getPublicId($path));
        } catch (\Exception $e) {
            // Ignore if file doesn't exist
        }
    }

    public function deleteDirectory(string $path): void
    {
        try {
            $this->client->uploadApi()->deleteFolder($path);
        } catch (\Exception $e) {
            // Ignore if folder doesn't exist
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Cloudinary doesn't need explicit directory creation
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Cloudinary handles this through upload options
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        try {
            $info = $this->client->uploadApi()->asset($path);
            return new FileAttributes($path, null, null, null, $info['resource_type'] . '/' . $info['format']);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        try {
            $info = $this->client->uploadApi()->asset($path);
            return new FileAttributes($path, null, null, strtotime($info['created_at']));
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $info = $this->client->uploadApi()->asset($path);
            return new FileAttributes($path, $info['bytes']);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        try {
            $results = $this->client->uploadApi()->resources([
                'type' => 'upload',
                'prefix' => $path,
                'max_results' => 500
            ]);

            foreach ($results['resources'] as $resource) {
                yield new FileAttributes(
                    $resource['public_id'],
                    $resource['bytes'],
                    'public',
                    strtotime($resource['created_at']),
                    $resource['resource_type'] . '/' . $resource['format']
                );
            }
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->client->uploadApi()->rename(
                $this->getPublicId($source),
                $this->getPublicId($destination)
            );
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($source, $e->getMessage());
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $asset = $this->client->uploadApi()->asset($source);
            $this->client->uploadApi()->upload(
                $asset['secure_url'],
                ['public_id' => $this->getPublicId($destination)]
            );
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($source, $e->getMessage());
        }
    }

    protected function getPublicId(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}