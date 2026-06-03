<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssetControllerTest extends WebTestCase
{
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/media/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('アセット一覧', $crawler->filter('h1')->text());
    }

    public function testUploadPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/media/upload');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('アセットアップロード', $crawler->filter('h1')->text());
    }
}
