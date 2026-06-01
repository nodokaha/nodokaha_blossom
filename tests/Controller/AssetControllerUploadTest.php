<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssetControllerUploadTest extends WebTestCase
{
    public function testUploadFormDisplaysEncryptionKeyField(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('暗号化キー', $crawler->text());
        $this->assertStringContainsString('アセットをアップロード', $crawler->text());
    }

    public function testUploadFormDisplaysAssetTypeChoices(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('種別', $crawler->text());
        $this->assertStringContainsString('プロップ', $crawler->text());
        $this->assertStringContainsString('ワールド', $crawler->text());
        $this->assertStringContainsString('アバター', $crawler->text());
    }

    public function testUploadFormDisplaysFileLabel(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('ファイルを選択', $crawler->text());
    }

    public function testUploadFormDisplaysSubmitButton(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('アップロード', $crawler->text());
    }

    public function testUploadFormContainsEncryptionKeyInput(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        
        $html = $crawler->html();
        $this->assertStringContainsString('encryptionKey', $html);
        $this->assertStringContainsString('type="text"', $html);
    }

    public function testUploadPageStylesLoadCorrectly(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/basisvr/cdn/upload');

        $this->assertResponseIsSuccessful();
        
        $html = $crawler->html();
        $this->assertStringContainsString('asset-upload-container', $html);
        $this->assertStringContainsString('.form-label', $html);
    }
}
