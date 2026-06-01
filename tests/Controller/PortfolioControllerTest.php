<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PortfolioControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::ensureKernelShutdown();
    }

    public function testContentHomeDisplaysPrimaryRoutes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'BasisVR コンテンツ投稿');
        $this->assertSelectorTextContains('body', 'SECD VM');
        $this->assertSelectorTextContains('body', 'CONTENT SIGNAL');
    }

    public function testSecdVmPageRunsDefaultProgram(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tools/secd-vm');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SECD VM 簡易インタプリタ');
        $this->assertSelectorTextContains('body', '公開: コンテンツ確認完了');
    }

    public function testContentSignalPageDisplaysScore(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tools/content-signal');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CONTENT SIGNAL');
        $this->assertSelectorTextContains('body', 'Score');
    }
}
