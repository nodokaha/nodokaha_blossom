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

    public function store(UploadedFile $uploadedFile, string $encryptionKey): AssetFile
    {
        $originalExtension = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION);
        $extension = $originalExtension !== '' ? strtolower($originalExtension) : ($uploadedFile->guessExtension() ?: 'bin');
        $storageKey = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);

        $clientMimeType = $uploadedFile->getClientMimeType();

        $movedFile = $uploadedFile->move($this->uploadDirectory, $storageKey);
        $mimeType = $clientMimeType ?: ($movedFile->getMimeType() ?? 'application/octet-stream');
        $size = $movedFile->getSize() ?: 0;

        return (new AssetFile())
            ->setStorageKey($storageKey)
            ->setOriginalName($uploadedFile->getClientOriginalName())
            ->setMimeType($mimeType)
            ->setSize($size)
            ->setEncryptionKey($encryptionKey);
    }

    public function resolvePath(string $storageKey): string
    {
        return rtrim($this->uploadDirectory, '/').'/'.$storageKey;
    }
}
