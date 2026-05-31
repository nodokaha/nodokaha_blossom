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

    public function testReviewHomeDisplaysPrimaryRoutes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'プロジェクト見直しブログ');
        $this->assertSelectorTextContains('body', 'SECD VM');
        $this->assertSelectorTextContains('body', 'REVIEW SIGNAL');
    }

    public function testSecdVmPageRunsDefaultProgram(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tools/secd-vm');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SECD VM 簡易インタプリタ');
        $this->assertSelectorTextContains('body', '公開: 読み直し完了');
    }

    public function testReviewSignalPageDisplaysScore(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tools/review-signal');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'REVIEW SIGNAL');
        $this->assertSelectorTextContains('body', 'Score');
    }
}
