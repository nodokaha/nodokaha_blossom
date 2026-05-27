<?php

namespace App\Tests\Service;

use App\Service\Asset\AssetStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AssetStorageServiceTest extends TestCase
{
    private string $uploadDirectory;

    protected function setUp(): void
    {
        $this->uploadDirectory = sys_get_temp_dir().'/basisvr_asset_storage_test';
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->uploadDirectory)) {
            return;
        }

        foreach (glob($this->uploadDirectory.'/*') ?: [] as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        rmdir($this->uploadDirectory);
    }

    public function testStoreMovesFileAndReturnsMetadata(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'asset_service_');
        self::assertNotFalse($tmpFile);
        file_put_contents($tmpFile, 'abcd');

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'avatar.bin',
            'application/octet-stream',
            null,
            true
        );

        $service = new AssetStorageService($this->uploadDirectory);
        $assetFile = $service->store($uploadedFile);

        $this->assertSame('avatar.bin', $assetFile->getOriginalName());
        $this->assertSame('application/octet-stream', $assetFile->getMimeType());
        $this->assertSame(4, $assetFile->getSize());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.bin$/', $assetFile->getStorageKey());
        $this->assertFileExists($this->uploadDirectory.'/'.$assetFile->getStorageKey());
    }

    public function testResolvePathReturnsExpectedPath(): void
    {
        $service = new AssetStorageService($this->uploadDirectory.'/');

        $this->assertSame(
            $this->uploadDirectory.'/sample.glb',
            $service->resolvePath('sample.glb')
        );
    }
}
