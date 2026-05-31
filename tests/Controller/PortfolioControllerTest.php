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

    public function testPortfolioHomeDisplaysPrimaryRoutes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'NODOKAHA BLOSSOM');
        $this->assertSelectorTextContains('body', 'SECD VM');
        $this->assertSelectorTextContains('body', 'BLOOM SIGNAL');
    }

    public function testSecdVmPageRunsDefaultProgram(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio/secd-vm');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SECD VM 簡易インタプリタ');
        $this->assertSelectorTextContains('body', '開花: プロトタイプへ');
    }

    public function testBloomSignalPageDisplaysScore(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio/bloom-signal');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'BLOOM SIGNAL');
        $this->assertSelectorTextContains('body', 'Score');
    }
}
