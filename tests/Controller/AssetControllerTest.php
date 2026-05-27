<?php

namespace App\Tests\Controller;

use App\Entity\AssetFile;
use App\Repository\AssetFileRepository;
use App\Service\Asset\AssetStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AssetControllerTest extends WebTestCase
{
    public function testAssetIndexDisplaysFiles(): void
    {
        $client = static::createClient();

        $asset = (new AssetFile())
            ->setStorageKey('abc123.bin')
            ->setOriginalName('world.glb')
            ->setMimeType('model/gltf-binary')
            ->setSize(1024);

        $repo = $this->createMock(AssetFileRepository::class);
        $repo->method('findRecent')->willReturn([$asset]);
        static::getContainer()->set(AssetFileRepository::class, $repo);

        $client->request('GET', '/basisvr/cdn');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Asset CDN');
        $this->assertSelectorTextContains('body', 'world.glb');
    }

    public function testAssetUploadSubmitsAndRedirects(): void
    {
        $client = static::createClient();

        $uploadedEntity = (new AssetFile())
            ->setStorageKey('stored.bin')
            ->setOriginalName('test.bin')
            ->setMimeType('application/octet-stream')
            ->setSize(4);

        $storage = $this->createMock(AssetStorageService::class);
        $storage->expects($this->once())->method('store')->with($this->isInstanceOf(UploadedFile::class))->willReturn($uploadedEntity);
        static::getContainer()->set(AssetStorageService::class, $storage);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($uploadedEntity);
        $entityManager->expects($this->once())->method('flush');
        static::getContainer()->set(EntityManagerInterface::class, $entityManager);

        $tmp = tempnam(sys_get_temp_dir(), 'asset_upload_');
        file_put_contents($tmp, 'test');
        $uploadedFile = new UploadedFile($tmp, 'test.bin', 'application/octet-stream', null, true);

        $crawler = $client->request('GET', '/basisvr/cdn/upload');
        $form = $crawler->selectButton('アップロード')->form();
        $form['asset_upload[asset]']->upload($uploadedFile);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/cdn');
    }
}
