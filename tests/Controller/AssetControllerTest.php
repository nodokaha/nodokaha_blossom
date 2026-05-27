<?php

namespace App\Tests\Controller;

use App\Entity\AssetFile;
use App\Repository\AssetFileRepository;
use App\Service\Asset\AssetStorageService;
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
        $this->assertSelectorTextContains('body > h1', 'Asset CDN');
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

        $tmp = tempnam(sys_get_temp_dir(), 'asset_upload_');
        file_put_contents($tmp, "GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xFF\xFF\xFF!\xF9\x04\x00\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;");
        $uploadedFile = new UploadedFile($tmp, 'test.gif', 'image/gif', null, true);

        $crawler = $client->request('GET', '/basisvr/cdn/upload');
        $form = $crawler->selectButton('アップロード')->form();
        $form['asset_upload[asset]']->upload($uploadedFile);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/cdn');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
