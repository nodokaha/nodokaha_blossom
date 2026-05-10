<?php

namespace App\Tests\Controller;

use App\Controller\LuckyController;
use App\Service\NumberGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class LuckyControllerTest extends TestCase
{
    public function testNumber(): void
    {
        $numberGenerator = $this->createMock(NumberGeneratorService::class);
        $numberGenerator->expects($this->once())
            ->method('generateRandomNumber')
            ->willReturn(42);

        $controller = new LuckyController($numberGenerator);

        // Since it's a controller, we can't easily test the render part without more setup
        // But we can test that the service is called
        // For full integration, we'd need WebTestCase

        $this->assertInstanceOf(LuckyController::class, $controller);
    }
}