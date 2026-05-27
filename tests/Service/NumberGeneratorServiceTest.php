<?php

namespace App\Tests\Service;

use App\Service\NumberGeneratorService;
use PHPUnit\Framework\TestCase;

class NumberGeneratorServiceTest extends TestCase
{
    public function testGenerateRandomNumber(): void
    {
        $service = new NumberGeneratorService();

        $number = $service->generateRandomNumber(0, 100);

        $this->assertIsInt($number);
        $this->assertGreaterThanOrEqual(0, $number);
        $this->assertLessThanOrEqual(100, $number);
    }

    public function testGenerateRandomNumberWithCustomRange(): void
    {
        $service = new NumberGeneratorService();

        $number = $service->generateRandomNumber(10, 20);

        $this->assertIsInt($number);
        $this->assertGreaterThanOrEqual(10, $number);
        $this->assertLessThanOrEqual(20, $number);
    }
}