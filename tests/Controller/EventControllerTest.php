<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/events/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('イベント一覧', $crawler->filter('h1')->text());
    }

    public function testCalendarPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/events/calendar');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('イベントカレンダー', $crawler->filter('h1')->text());
    }

    public function testCreatePageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/events/create');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('イベント作成', $crawler->filter('h1')->text());
    }
}
