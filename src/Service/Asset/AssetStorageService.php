<?php

declare(strict_types=1);

namespace App\Service\Asset;

use App\Entity\AssetFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetStorageService
{
    public function __construct(private readonly string $uploadDirectory)
    {
    }

    public function store(UploadedFile $uploadedFile): AssetFile
    {
        $extension = $uploadedFile->guessExtension() ?: 'bin';
        $storageKey = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $uploadedFile->move($this->uploadDirectory, $storageKey);

        return (new AssetFile())
            ->setStorageKey($storageKey)
            ->setOriginalName($uploadedFile->getClientOriginalName())
            ->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream')
            ->setSize($uploadedFile->getSize() ?: 0);
    }

    public function resolvePath(string $storageKey): string
    {
        return rtrim($this->uploadDirectory, '/').'/'.$storageKey;
    }
}
